<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\Radiology\RadiologyResultImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DicomController extends Controller
{
    /**
     * DICOM / PACS Bridge API
     *
     * Provides endpoints for:
     *   - Querying studies from the local modality worklist
     *   - Receiving DICOM images pushed from modalities (Store SCP simulation)
     *   - Retrieving study/series metadata for PACS integration
     *   - Serving images to HL7 / DICOM viewers
     *
     * Compatible modalities (common in Philippine hospitals):
     *   X-Ray: Fuji, Siemens, Philips, GE, Shimadzu
     *   CT: GE, Siemens, Canon/Toshiba, Philips
     *   MRI: GE, Siemens, Philips, Canon
     *   Ultrasound: GE, Siemens, Mindray, Samsung, Esaote, Fuji
     *   Mammo: GE, Siemens, Hologic, Fuji, IMS Giotto
     */

    private function modalityConfig(): array
    {
        return config('dicom');
    }

    /**
     * GET /api/dicom/studies?patient_id=&accession_number=&study_uid=
     * Query local studies (worklist).
     */
    public function queryStudies(Request $request): JsonResponse
    {
        $query = RadiologyResultImage::with('radiologyRequestItem.radiologyRequest')
            ->where('history', 1);

        if ($request->filled('patient_id')) {
            $patientId = $request->patient_id;
            $query->whereHas('radiologyRequestItem.radiologyRequest', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId)
                  ->orWhereHas('erAdmission', fn ($q) => $q->where('patient_id', $patientId))
                  ->orWhereHas('opdConsultation', fn ($q) => $q->where('patient_id', $patientId))
                  ->orWhereHas('admission', fn ($q) => $q->where('patient_id', $patientId));
            });
        }

        if ($request->filled('accession_number')) {
            $query->whereHas('radiologyRequestItem.radiologyRequest', fn ($q) =>
                $q->where('request_code', $request->accession_number)
            );
        }

        if ($request->filled('study_uid')) {
            $query->where('dicom_study_uid', $request->study_uid);
        }

        $studies = $query->get()->groupBy(function ($img) {
            return $img->dicom_study_uid ?: 'local_' . $img->radiology_request_item_id;
        })->map(function ($images, $studyUid) {
            $first = $images->first();
            $item = $first->radiologyRequestItem;
            $req = $item->radiologyRequest ?? null;
            return [
                'study_uid' => $studyUid,
                'accession_number' => $req?->request_code,
                'patient_name' => $item->patient_name,
                'study_description' => $item->exam_name,
                'modality' => $item->modality_name,
                'study_date' => $item->result_date?->toIso8601String(),
                'referring_physician' => $req?->requesting_person,
                'num_series' => $images->pluck('dicom_series_uid')->unique()->count(),
                'num_instances' => $images->count(),
                'images' => $images->map(fn ($img) => [
                    'id' => $img->id,
                    'sop_uid' => $img->dicom_sop_uid,
                    'series_uid' => $img->dicom_series_uid,
                    'url' => url('/api/dicom/images/' . $img->id),
                    'thumbnail' => url('/api/dicom/images/' . $img->id . '/thumbnail'),
                ]),
            ];
        })->values();

        return response()->json(['studies' => $studies, 'count' => $studies->count()]);
    }

    /**
     * GET /api/dicom/images/{id}
     * Serve a stored DICOM or image file.
     */
    public function serveImage(int $id): mixed
    {
        $img = RadiologyResultImage::findOrFail($id);
        $path = $img->path;

        if (!Storage::disk('public')->exists($path)) {
            $path = $img->path;
            if (!Storage::exists($path)) {
                return response()->json(['error' => 'Image not found'], 404);
            }
        }

        $fileContent = Storage::disk('public')->get($path);
        $mime = $img->mime_type ?? 'application/octet-stream';

        if (str_contains($mime, 'dicom') || str_ends_with($img->filename ?? '', '.dcm')) {
            $mime = 'application/dicom';
        }

        return response($fileContent, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . ($img->original_name ?? $img->filename) . '"',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * GET /api/dicom/images/{id}/thumbnail
     * Serve a small thumbnail for gallery view.
     */
    public function thumbnail(int $id): mixed
    {
        $img = RadiologyResultImage::findOrFail($id);
        $path = $img->path;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $mime = $img->mime_type ?? 'image/jpeg';
        $fileContent = Storage::disk('public')->get($path);

        return response($fileContent, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="thumb_' . ($img->original_name ?? $img->filename) . '"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * POST /api/dicom/store
     * Receive a DICOM/image pushed from a modality (Store SCP simulation).
     *
     * Expected: multipart form with 'image_file' + metadata fields.
     */
    public function storeImage(Request $request): JsonResponse
    {
        $request->validate([
            'radiology_request_item_id' => 'required|integer|exists:radiology_request_items,id',
            'image_file' => 'required|file|max:51200',
            'dicom_study_uid' => 'nullable|string|max:255',
            'dicom_series_uid' => 'nullable|string|max:255',
            'dicom_sop_uid' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $file = $request->file('image_file');
        $path = $file->store('dicom/studies', 'public');

        $img = RadiologyResultImage::create([
            'radiology_request_item_id' => $request->radiology_request_item_id,
            'filename' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'dicom_study_uid' => $request->dicom_study_uid,
            'dicom_series_uid' => $request->dicom_series_uid,
            'dicom_sop_uid' => $request->dicom_sop_uid,
            'is_primary' => $request->boolean('is_primary', false),
            'history' => 1,
            'created_by' => $request->user()?->id ?? 1,
            'created_date' => now(),
            'updated_by' => $request->user()?->id ?? 1,
            'updated_date' => now(),
        ]);

        return response()->json([
            'message' => 'Image stored successfully.',
            'image' => $img,
            'url' => url('/api/dicom/images/' . $img->id),
        ], 201);
    }

    /**
     * GET /api/dicom/modalities
     * List configured modalities for DICOM communication.
     */
    public function modalities(): JsonResponse
    {
        $config = $this->modalityConfig();
        return response()->json([
            'local_aet' => $config['aet_title'],
            'pacs' => [
                'host' => $config['pacs_host'],
                'port' => $config['pacs_port'],
                'aet' => $config['pacs_aet'],
            ],
            'supported_modalities' => [
                'CR' => 'Computed Radiography (Fuji, Siemens, Philips, GE, Shimadzu)',
                'DX' => 'Digital Radiography',
                'CT' => 'Computed Tomography (GE, Siemens, Canon/Toshiba, Philips)',
                'MR' => 'Magnetic Resonance (GE, Siemens, Philips, Canon)',
                'US' => 'Ultrasound (GE, Siemens, Mindray, Samsung, Esaote, Fuji)',
                'MG' => 'Mammography (GE, Siemens, Hologic, Fuji, IMS Giotto)',
                'NM' => 'Nuclear Medicine',
                'XA' => 'X-Ray Angiography',
                'RF' => 'Radiofluoroscopy',
                'DXA' => 'DEXA Bone Densitometry',
            ],
        ]);
    }

    /**
     * GET /api/dicom/worklist?date=&modality=&status=
     * Modality Worklist (MWL) simulation — returns exam items pending imaging.
     */
    public function worklist(Request $request): JsonResponse
    {
        $query = RadiologyRequestItem::with([
            'radiologyRequest.patient',
            'radiologyRequest.erAdmission.patient',
            'radiologyRequest.opdConsultation.patient',
            'radiologyRequest.admission.patient',
            'examType',
        ])->whereIn('status', ['Pending', 'Approved']);

        if ($request->filled('modality')) {
            $query->where('modality_name', 'like', '%' . $request->modality . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_date', $request->date);
        }

        $items = $query->limit(50)->get()->map(function ($item) {
            $patient = $item->radiologyRequest?->patient
                ?? $item->radiologyRequest?->erAdmission?->patient
                ?? $item->radiologyRequest?->opdConsultation?->patient
                ?? $item->radiologyRequest?->admission?->patient;
            return [
                'id' => $item->id,
                'accession_number' => $item->radiologyRequest?->request_code,
                'patient_name' => $patient?->full_name ?? 'Unknown',
                'patient_id' => $patient?->patient_code ?? $patient?->id,
                'patient_birthdate' => $patient?->date_of_birth?->format('Y-m-d'),
                'patient_gender' => $patient?->gender?->name ?? '',
                'exam_name' => $item->exam_name,
                'modality' => $item->modality_name,
                'requesting_physician' => $item->radiologyRequest?->requesting_person,
                'request_date' => $item->created_date?->toIso8601String(),
                'status' => $item->status,
            ];
        });

        return response()->json(['worklist' => $items, 'count' => $items->count()]);
    }
}
