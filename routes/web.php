<?php

use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────────────────────────

Route::get('/login', fn () => view('pages.auth.login'))->middleware('throttle:10,1')->name('login');

Route::get('/book-appointment', \App\Livewire\Public\AppointmentBooking::class)->name('public.book');

// ── JWT-protected web routes ──────────────────────────────────────────────────

    Route::get('/', fn () => view('pages.dashboard'))->middleware('permission:dashboard.view')->name('dashboard');

    Route::get('/transactions', fn () => view('pages.dashboard'))->middleware('permission:billing.view')->name('transactions');

    Route::get('/reports', fn () => view('pages.reports'))->middleware('permission:reports.view')->name('reports');
    Route::get('/reports/print/{type?}', [\App\Http\Controllers\PdfReportController::class, 'hospitalReportsPrintView'])->middleware('permission:reports.view')->name('reports.print');
    Route::get('/reports/pdf/{type?}', [\App\Http\Controllers\PdfReportController::class, 'hospitalReportsPdf'])->middleware('permission:reports.view')->name('reports.pdf');

    Route::get('/settings', fn () => view('pages.settings'))->middleware('permission:admin.settings')->name('settings');

    // ── Master Routes ──────────────────────────────────────────────────────────
    Route::prefix('masters')->name('masters.')->group(function () {
        Route::get('company', fn () => view('pages.masters.tenant'))->middleware('permission:admin.tenants')->name('tenant');
        Route::get('department', fn () => view('pages.masters.department'))->middleware('permission:admin.departments')->name('department');
        Route::get('users', fn () => view('pages.masters.users'))->middleware('permission:admin.users')->name('users');

        Route::middleware('permission:admin.masters')->group(function () {
            Route::get('country', fn () => view('pages.masters.country'))->name('country');
            Route::get('region', fn () => view('pages.masters.region'))->name('region');
            Route::get('province', fn () => view('pages.masters.province'))->name('province');
            Route::get('city', fn () => view('pages.masters.city'))->name('city');
            Route::get('barangay', fn () => view('pages.masters.barangay'))->name('barangay');
            Route::get('religion', fn () => view('pages.masters.religion'))->name('religion');
            Route::get('gender', fn () => view('pages.masters.gender'))->name('gender');
            Route::get('civil-status', fn () => view('pages.masters.civil-status'))->name('civil-status');
            Route::get('nationality', fn () => view('pages.masters.nationality'))->name('nationality');
            Route::get('occupation', fn () => view('pages.masters.occupation'))->name('occupation');
            Route::get('blood-type', fn () => view('pages.masters.blood-type'))->name('blood-type');
            Route::get('relationship', fn () => view('pages.masters.relationship'))->name('relationship');
            Route::get('hospital', fn () => view('pages.masters.hospital'))->name('hospital');
            Route::get('permissions', fn () => view('pages.masters.permissions'))->name('permissions');
        });
    });

    // ── Admin Routes ───────────────────────────────────────────────────────────
    Route::get('/admin/roles', fn () => view('pages.admin.roles'))->middleware('permission:admin.masters')->name('admin.roles');
    Route::get('/admin/role-permissions', fn () => view('pages.admin.role-permissions'))->middleware('permission:admin.masters')->name('admin.role-permissions');
    Route::get('/admin/audit-log', fn () => view('pages.admin.audit-log'))->middleware('permission:admin.masters')->name('admin.audit-log');
    Route::get('/admin/users/{userId}/permissions', fn (int $userId) => view('pages.admin.user-permissions', ['userId' => $userId]))->middleware('permission:admin.users')->name('admin.users.permissions');

    // ── Transfer Routes ────────────────────────────────────────────────────────
    Route::prefix('transfers')->name('transfers.')->middleware('permission:admission.view')->group(function () {
        Route::get('/', fn () => view('pages.transfer.hospital-transfers'))->name('index');
    });

    // ── Patient Registration ───────────────────────────────────────────────────
    Route::get('/patients', fn () => view('pages.patient-registration'))->middleware('permission:patient.view')->name('patients');

    // ── Laboratory Routes ──────────────────────────────────────────────────────
    Route::prefix('lab')->name('lab.')->group(function () {
        Route::middleware('permission:lab.manage')->group(function () {
            Route::get('sections', fn () => view('pages.lab.sections'))->name('sections');
            Route::get('units', fn () => view('pages.lab.units'))->name('units');
            Route::get('specimen-types', fn () => view('pages.lab.specimen-types'))->name('specimen-types');
            Route::get('instruments', fn () => view('pages.lab.instruments'))->name('instruments');
            Route::get('tests', fn () => view('pages.lab.tests'))->name('tests');
            Route::get('panels', fn () => view('pages.lab.panels'))->name('panels');
            Route::get('reference-ranges', fn () => view('pages.lab.reference-ranges'))->name('reference-ranges');
        });
        Route::get('dispensing', fn () => view('pages.lab.dispensing'))->middleware('permission:lab.dispense')->name('dispensing');
        Route::get('results', fn () => view('pages.lab.results'))->middleware('permission:lab.result')->name('results');
        Route::get('result/{id}/print', function (int $id) {
            $item = App\Models\LabRequestItem::with(['labRequest.patient', 'labRequest.requestingDoctor', 'labTest.labUnit', 'performedBy', 'verifiedBy', 'validatedBy'])->findOrFail($id);
            return view('pages.lab.result-print', compact('item'));
        })->middleware('permission:lab.result')->name('result.print');
    });

    // ── CSR / Supply Routes ────────────────────────────────────────────────────
    Route::prefix('supply')->name('supply.')->group(function () {
        Route::middleware('permission:csr.manage')->group(function () {
            Route::get('units', fn () => view('pages.supply.units'))->name('units');
            Route::get('categories', fn () => view('pages.supply.categories'))->name('categories');
            Route::get('suppliers', fn () => view('pages.supply.suppliers'))->name('suppliers');
            Route::get('stock-locations', fn () => view('pages.supply.stock-locations'))->name('stock-locations');
            Route::get('items', fn () => view('pages.supply.items'))->name('items');
            Route::get('inventory', fn () => view('pages.supply.inventory'))->name('inventory');
            Route::get('stock-adjustments', fn () => view('pages.supply.stock-adjustments'))->name('stock-adjustments');
        });
        Route::get('dispensing', fn () => view('pages.supply.dispensing'))->middleware('permission:csr.dispense')->name('dispensing');

        Route::get('request/{id}/print', function (int $id) {
            $request = \App\Models\CsrRequest::withoutGlobalScopes()
                ->with([
                    'items.item',
                    'requestingDepartment', 'patient',
                ])
                ->findOrFail($id);
            $tenant = \App\Models\Tenant::withoutGlobalScopes()
                ->find(request()->attributes->get('tenant_id') ?? 1);
            return view('pages.supply.request-print', compact('request', 'tenant'));
        })->middleware('permission:csr.dispense')->name('request.print');

        Route::get('request/{id}/encounter-print', function (int $id) {
            $request = \App\Models\CsrRequest::withoutGlobalScopes()
                ->with(['patient'])
                ->findOrFail($id);

            $encounterQuery = \App\Models\CsrRequest::withoutGlobalScopes()
                ->with(['items.item', 'requestingDepartment'])
                ->where('history', 1);

            $encounterColumn = null;
            foreach (['er_admission_id', 'opd_consultation_id', 'admission_id', 'dialysis_session_id', 'or_schedule_id', 'dr_schedule_id', 'ob_consultation_id'] as $col) {
                if ($request->{$col}) {
                    $encounterColumn = $col;
                    break;
                }
            }

            if ($encounterColumn && $request->{$encounterColumn}) {
                $encounterRequests = (clone $encounterQuery)
                    ->where($encounterColumn, $request->{$encounterColumn})
                    ->orderBy('created_date')
                    ->get();
            } else {
                $encounterRequests = collect([$request]);
            }

            $tenant = \App\Models\Tenant::withoutGlobalScopes()
                ->find(request()->attributes->get('tenant_id') ?? 1);

            return view('pages.supply.encounter-print', compact('request', 'encounterRequests', 'tenant'));
        })->middleware('permission:csr.dispense')->name('request.encounter-print');
    });

    // ── Pharmacy Routes ────────────────────────────────────────────────────────
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::middleware('permission:pharmacy.manage')->group(function () {
            Route::get('drugs', fn () => view('pages.pharmacy.drugs'))->name('drugs');
            Route::get('drug-batches', fn () => view('pages.pharmacy.drug-batches'))->name('drug-batches');
            Route::get('inventory', fn () => view('pages.pharmacy.inventory'))->name('inventory');
            Route::get('purchase-orders', fn () => view('pages.pharmacy.purchase-orders'))->name('purchase-orders');
            Route::get('stock-adjustments', fn () => view('pages.pharmacy.stock-adjustments'))->name('stock-adjustments');
        });
        Route::get('dispensing', fn () => view('pages.pharmacy.dispensing'))->middleware('permission:pharmacy.dispense')->name('dispensing');
        Route::get('patient-medication-record', fn () => view('pages.pharmacy.patient-medication-record'))->middleware('permission:pharmacy.view')->name('patient-medication-record');
        Route::get('reports', fn () => view('pages.pharmacy.reports'))->middleware('permission:reports.view')->name('reports');
        Route::get('request/{id}/print', function (int $id) {
            $request = \App\Models\Pharmacy\PharmacyRequest::withoutGlobalScopes()
                ->with([
                    'items.drug', 'items.drugBatch',
                    'requestingDepartment', 'requestingDoctor',
                    'patient',
                ])
                ->findOrFail($id);
            $tenant = \App\Models\Tenant::withoutGlobalScopes()
                ->find(request()->attributes->get('tenant_id') ?? 1);
            return view('pages.pharmacy.request-print', compact('request', 'tenant'));
        })->middleware('permission:pharmacy.view')->name('request.print');

        Route::get('request/{id}/encounter-print', function (int $id) {
            $request = \App\Models\Pharmacy\PharmacyRequest::withoutGlobalScopes()
                ->with(['patient'])
                ->findOrFail($id);

            $encounterQuery = \App\Models\Pharmacy\PharmacyRequest::withoutGlobalScopes()
                ->with(['items.drug', 'items.drugBatch', 'requestingDepartment', 'requestingDoctor'])
                ->where('history', 1);

            $encounterColumn = null;
            foreach (['er_admission_id', 'opd_consultation_id', 'admission_id', 'dialysis_session_id', 'or_schedule_id', 'dr_schedule_id', 'ob_consultation_id'] as $col) {
                if ($request->{$col}) {
                    $encounterColumn = $col;
                    break;
                }
            }

            if ($encounterColumn && $request->{$encounterColumn}) {
                $encounterRequests = (clone $encounterQuery)
                    ->where($encounterColumn, $request->{$encounterColumn})
                    ->orderBy('created_date')
                    ->get();
            } else {
                $encounterRequests = collect([$request]);
            }

            $tenant = \App\Models\Tenant::withoutGlobalScopes()
                ->find(request()->attributes->get('tenant_id') ?? 1);

            return view('pages.pharmacy.encounter-print', compact('request', 'encounterRequests', 'tenant'));
        })->middleware('permission:pharmacy.view')->name('request.encounter-print');
    });

    // ── Procedures Routes ──────────────────────────────────────────────────────
    Route::prefix('procedures')->name('procedures.')->group(function () {
        Route::middleware('permission:procedure.manage')->group(function () {
            Route::get('categories', fn () => view('pages.procedures.categories'))->name('categories');
            Route::get('procedures', fn () => view('pages.procedures.procedures'))->name('procedures');
        });
        Route::get('dispensing', fn () => view('pages.procedures.dispensing'))->middleware('permission:procedure.dispense')->name('dispensing');
    });

    // ── Radiology Routes ───────────────────────────────────────────────────────
    Route::prefix('radiology')->name('radiology.')->group(function () {
        Route::middleware('permission:radiology.manage')->group(function () {
            Route::get('modalities', fn () => view('pages.radiology.modalities'))->name('modalities');
            Route::get('body-parts', fn () => view('pages.radiology.body-parts'))->name('body-parts');
            Route::get('exam-types', fn () => view('pages.radiology.exam-types'))->name('exam-types');
        });
        Route::get('dispensing', fn () => view('pages.radiology.dispensing'))->middleware('permission:radiology.dispense')->name('dispensing');
        Route::get('results', fn () => view('pages.radiology.results'))->middleware('permission:radiology.result')->name('results');
        Route::get('result/{id}/print', function (int $id) {
            $item = App\Models\Radiology\RadiologyRequestItem::with(['radiologist', 'performedBy', 'radiologyRequest'])->findOrFail($id);
            $item->load('images');
            return view('pages.radiology.result-print', compact('item'));
        })->middleware('permission:radiology.result')->name('result.print');
    });

    // ── DICOM / PACS API Routes ────────────────────────────────────────────────
    Route::prefix('api/dicom')->name('api.dicom.')->middleware('permission:radiology.result')->group(function () {
        Route::get('studies', [App\Http\Controllers\Api\DicomController::class, 'queryStudies'])->name('studies');
        Route::get('modalities', [App\Http\Controllers\Api\DicomController::class, 'modalities'])->name('modalities');
        Route::get('worklist', [App\Http\Controllers\Api\DicomController::class, 'worklist'])->name('worklist');
        Route::get('images/{id}', [App\Http\Controllers\Api\DicomController::class, 'serveImage'])->name('images.serve');
        Route::get('images/{id}/thumbnail', [App\Http\Controllers\Api\DicomController::class, 'thumbnail'])->name('images.thumbnail');
        Route::post('store', [App\Http\Controllers\Api\DicomController::class, 'storeImage'])->name('images.store');
    });

    // ── Renal Care Routes ──────────────────────────────────────────────────────
    Route::prefix('renal-care')->name('renal-care.')->middleware('permission:renal.view')->group(function () {
        Route::get('dialysis-types', fn () => view('pages.renal-care.dialysis-types'))->name('dialysis-types');
        Route::get('dialysis-sessions', fn () => view('pages.renal-care.dialysis-sessions'))->name('dialysis-sessions');
    });

    // ── ICU Routes ─────────────────────────────────────────────────────────────
    Route::prefix('icu')->name('icu.')->middleware('permission:admission.view')->group(function () {
        Route::get('wards', fn () => view('pages.icu.wards'))->name('wards');
        Route::get('beds', fn () => view('pages.icu.beds'))->name('beds');
    });

    // ── MICU Routes ────────────────────────────────────────────────────────────
    Route::prefix('micu')->name('micu.')->middleware('permission:admission.view')->group(function () {
        Route::get('patients', fn () => view('pages.micu.patients'))->name('patients');
        Route::get('assessment-parameters', fn () => view('pages.micu.assessment-parameters'))->name('assessment-parameters');
    });

    // ── Delivery Routes ────────────────────────────────────────────────────────
    Route::prefix('delivery')->name('delivery.')->middleware('permission:delivery.view')->group(function () {
        Route::get('types', fn () => view('pages.delivery.types'))->name('types');
        Route::get('complications', fn () => view('pages.delivery.complications'))->name('complications');
        Route::get('rooms', fn () => view('pages.delivery.rooms'))->name('rooms');
        Route::get('schedule', fn () => view('pages.delivery.schedule'))->name('schedule');
    });

    // ── ER Routes ──────────────────────────────────────────────────────────────
    Route::prefix('er')->name('er.')->group(function () {
        Route::get('triage', fn () => view('pages.er.triage'))->middleware('permission:er.triage')->name('triage');
        Route::middleware('permission:er.manage')->group(function () {
            Route::get('acuity-levels', fn () => view('pages.er.acuity-levels'))->name('acuity-levels');
            Route::get('mobility-statuses', fn () => view('pages.er.mobility-statuses'))->name('mobility-statuses');
            Route::get('zones', fn () => view('pages.er.zones'))->name('zones');
            Route::get('beds', fn () => view('pages.er.beds'))->name('beds');
            Route::get('area', fn () => view('pages.er.area'))->name('area');
            Route::get('admission/{admission}/eeclpdf', function (\App\Models\Er\ErAdmission $admission) {
                $admission->loadMissing(['patient.philhealthMember', 'attendingPhysician', 'oecbEntries', 'triageRecord.acuityLevel', 'erBed.zone']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.er.eeclpdf', compact('admission'));
                return $pdf->download('eecl-' . $admission->admission_code . '.pdf');
            })->name('admission.eeclpdf');
        });
    });

    // ── Admission Routes ───────────────────────────────────────────────────────
    Route::get('/admission', fn () => view('pages.admission.admission'))->middleware('permission:admission.view')->name('admission');

    // ── Nursing Routes ─────────────────────────────────────────────────────────
    Route::get('/nursing', fn () => view('pages.nursing.nursing'))->middleware('permission:nursing.view')->name('nursing');

    // ── Billing Routes ─────────────────────────────────────────────────────────
    Route::get('/billing', fn () => view('pages.billing.billing'))->middleware('permission:billing.view')->name('billing');
    Route::get('/billing/fund-sources', fn () => view('pages.billing.fund-sources'))->middleware('permission:billing.manage')->name('billing.fund-sources');
    Route::get('/billing/insurance-providers', fn () => view('pages.billing.insurance-providers'))->middleware('permission:billing.manage')->name('billing.insurance-providers');
    Route::get('/billing/invoice/{id}/print', function (int $id) {
        $invoice = \App\Models\Billing\BillingInvoice::with(['patient', 'erAdmission', 'admission', 'items', 'payments', 'manualCharges'])->findOrFail($id);
        return view('pages.billing.invoice-print', compact('invoice'));
    })->middleware('permission:billing.view')->name('billing.invoice.print');
    Route::get('/billing/invoice/{id}/pdf', [\App\Http\Controllers\PdfReportController::class, 'billingInvoicePdf'])->middleware('permission:billing.view')->name('billing.invoice.pdf');
    Route::get('/billing/payment/{id}/receipt', function (int $id) {
        $payment = \App\Models\Billing\BillingPayment::with(['invoice.patient', 'invoice.items', 'invoice.manualCharges'])->findOrFail($id);
        return view('pages.billing.receipt-print', compact('payment'));
    })->middleware('permission:billing.view')->name('billing.payment.receipt');
    Route::get('/billing/statement/{patientId}/{mode}', function (int $patientId, string $mode) {
        $tid = request()->attributes->get('tenant_id') ?? 1;
        $patient = \App\Models\Patient::withoutGlobalScopes()->where('id', $patientId)->first();
        if (!$patient) abort(404, 'Patient not found.');
        $tenant = \App\Models\Tenant::withoutGlobalScopes()->find($tid);
        if (!$tenant) abort(404, 'Tenant not found.');
        $entries = \App\Models\Billing\PatientAccountEntry::getLedger($tid, $patientId);
        $balance = \App\Models\Billing\PatientAccountEntry::getPatientBalance($tid, $patientId);

        $ledgerData = $entries->map(fn ($e) => [
            'date' => $e->created_date?->format('M d, Y') ?? '—',
            'entry_type' => $e->entry_type,
            'source_type' => $e->source_type,
            'source_code' => $e->source_code ?? '—',
            'description' => $e->description ?? '—',
            'debit' => (float) $e->debit,
            'credit' => (float) $e->credit,
            'balance' => (float) $e->balance,
        ])->toArray();

        $summary = collect($ledgerData)
            ->filter(fn ($e) => $e['entry_type'] === 'charge')
            ->groupBy('source_type')
            ->map(fn ($items, $sourceType) => [
                'source_type' => $sourceType,
                'items' => $items,
                'total' => collect($items)->sum('debit'),
                'count' => count($items),
            ])->values()->toArray();

        $totalCharges = collect($ledgerData)->where('entry_type', 'charge')->sum('debit');
        $totalPayments = collect($ledgerData)->where('entry_type', 'payment')->sum('credit');

        $activeAdmission = \App\Models\Admission::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('patient_id', $patientId)
            ->where('history', 1)
            ->where('status', 'Admitted')
            ->with(['wardRoom', 'icuBed', 'roomHistories', 'attendingPhysician'])
            ->first();

        $daysStayed = null;
        $accruedRoomCharges = 0;
        $roomName = null;
        $roomRate = null;

        if ($activeAdmission) {
            $daysStayed = $activeAdmission->days_stayed;
            $accruedRoomCharges = $activeAdmission->computeAccruedRoomCharges();
            $activeHist = $activeAdmission->roomHistories->firstWhere('is_active', true);
            $roomName = $activeHist?->room_name ?? $activeAdmission->wardRoom?->name ?? $activeAdmission->icuBed?->name ?? 'Ward Room';
            if ($activeHist && $activeHist->ward_name) {
                $roomName .= ' (' . $activeHist->ward_name . ')';
            }
            $rate = (float) ($activeHist?->room_rate ?? $activeAdmission->wardRoom?->room_rate ?? $activeAdmission->icuBed?->room_rate ?? 0);
            $roomRate = 'PHP ' . number_format($rate, 2) . '/day';
        }

        $html = view('livewire.billing.statement-of-account', [
            'tenant' => $tenant, 'patient' => $patient, 'ledger' => $ledgerData,
            'summary' => $summary, 'totalCharges' => $totalCharges,
            'totalPayments' => $totalPayments, 'balance' => $balance,
            'activeAdmission' => $activeAdmission,
            'daysStayed' => $daysStayed,
            'roomName' => $roomName,
            'roomRate' => $roomRate,
            'accruedRoomCharges' => $accruedRoomCharges,
            'mode' => in_array($mode, ['summary', 'detailed']) ? $mode : 'detailed',
            'printedAt' => now()->format('M d, Y h:i A'),
        ])->render();

        if (request()->query('format') === 'pdf' || request()->has('download')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('letter', 'portrait')
                ->setOption('isRemoteEnabled', true)
                ->setOption('isHtml5ParserEnabled', true);

            $disposition = request()->has('download') ? 'attachment' : 'inline';
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="Statement_of_Account_' . str_replace(' ', '_', $patient->last_name . '_' . $patient->first_name) . '_' . $mode . '.pdf"',
                'X-Frame-Options' => 'SAMEORIGIN',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    })->middleware('permission:billing.view')->name('billing.statement');

    // ── OR Routes ──────────────────────────────────────────────────────────────
    Route::prefix('or')->name('or.')->middleware('permission:or.view')->group(function () {
        Route::get('rooms', fn () => view('pages.or.rooms'))->name('rooms');
        Route::get('schedule', fn () => view('pages.or.schedule'))->name('schedule');
    });

    // ── Ward Routes ────────────────────────────────────────────────────────────
    Route::prefix('ward')->name('ward.')->middleware('permission:admission.view')->group(function () {
        Route::get('wards', fn () => view('pages.ward.wards'))->name('wards');
        Route::get('room-types', fn () => view('pages.ward.room-types'))->name('room-types');
        Route::get('rooms', fn () => view('pages.ward.rooms'))->name('rooms');
    });

    // ── YAKAP Routes ───────────────────────────────────────────────────────────
    Route::prefix('yakap')->name('yakap.')->group(function () {
        Route::get('dashboard', \App\Livewire\Yakap\DashboardCrud::class)->middleware('permission:yakap.view')->name('dashboard');
        Route::middleware('permission:yakap.manage')->group(function () {
            Route::get('empanelments', \App\Livewire\Yakap\EmpanelmentCrud::class)->name('empanelments');
            Route::get('fpe-records', \App\Livewire\Yakap\FpeCrud::class)->name('fpe-records');
            Route::get('screenings', \App\Livewire\Yakap\ScreeningCrud::class)->name('screenings');
            Route::get('capitation', \App\Livewire\Yakap\CapitationCrud::class)->name('capitation');
        });
    });

    // ── Konsulta Routes ────────────────────────────────────────────────────────
    Route::prefix('konsulta')->name('konsulta.')->middleware('permission:konsulta.view')->group(function () {
        Route::get('/', \App\Livewire\Konsulta\KonsultaCrud::class)->name('index');
    });

    // ── OPD Routes ─────────────────────────────────────────────────────────────
    Route::prefix('opd')->name('opd.')->middleware('permission:opd.view')->group(function () {
        Route::get('visit-types', fn () => view('pages.opd.visit-types'))->name('visit-types');
        Route::get('consultation-types', fn () => view('pages.opd.consultation-types'))->name('consultation-types');
        Route::get('referral-sources', fn () => view('pages.opd.referral-sources'))->name('referral-sources');
        Route::get('services', fn () => view('pages.opd.services'))->name('services');
        Route::get('clinics', fn () => view('pages.opd.clinics'))->name('clinics');
        Route::get('complaints', fn () => view('pages.opd.complaints'))->name('complaints');
        Route::get('consultations', \App\Livewire\Opd\OpdConsultationCrud::class)->name('consultations');
        Route::get('consultation/{id}/pdf/prescription', [\App\Http\Controllers\PdfReportController::class, 'prescription'])->name('consultation.prescription.pdf');
        Route::get('consultation/{id}/pdf/certificate', [\App\Http\Controllers\PdfReportController::class, 'medicalCertificate'])->name('consultation.certificate.pdf');
        Route::get('consultation/{id}/pdf/summary', [\App\Http\Controllers\PdfReportController::class, 'consultationSummary'])->name('consultation.summary.pdf');
        Route::get('consultation/{id}/pdf/orders', [\App\Http\Controllers\PdfReportController::class, 'orderRequisition'])->name('consultation.orders.pdf');
    });

    // ── OB Routes ──────────────────────────────────────────────────────────────
    Route::prefix('ob')->name('ob.')->middleware('permission:ob.view')->group(function () {
        Route::get('visit-types', fn () => view('pages.ob.visit-types'))->name('visit-types');
        Route::get('fetal-presentations', fn () => view('pages.ob.fetal-presentations'))->name('fetal-presentations');
        Route::get('contraceptive-methods', fn () => view('pages.ob.contraceptive-methods'))->name('contraceptive-methods');
        Route::get('consultations', \App\Livewire\Ob\ObConsultationCrud::class)->name('consultations');
    });

    // ── Doctors Routes ─────────────────────────────────────────────────────────
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('dashboard', \App\Livewire\Doctors\DashboardCrud::class)->middleware('permission:doctors.dashboard')->name('dashboard');
        Route::get('schedule', \App\Livewire\Doctors\AppointmentScheduleCrud::class)->middleware('permission:doctors.schedule')->name('schedule');
        Route::get('patient/{type}/{id}', function (string $type, int $id) {
            return view('pages.doctors.patient', compact('type', 'id'));
        })->middleware('permission:doctors.chart')->name('patient');
    });

    // ── PhilHealth Routes ──────────────────────────────────────────────────────
    Route::prefix('philhealth')->name('philhealth.')->group(function () {
        Route::get('test-pipeline', \App\Http\Controllers\PhilHealthTestController::class)->name('test-pipeline');
        Route::middleware('permission:philhealth.view')->group(function () {
            Route::get('icd10-codes', fn () => view('pages.philhealth.icd10-codes'))->name('icd10-codes');
            Route::get('rvs-codes', fn () => view('pages.philhealth.rvs-codes'))->name('rvs-codes');
            Route::get('case-rates', fn () => view('pages.philhealth.case-rates'))->name('case-rates');
            Route::get('hc-professionals', fn () => view('pages.philhealth.hc-professionals'))->name('hc-professionals');
            Route::get('members', fn () => view('pages.philhealth.members'))->name('members');
            Route::get('eecl-items', fn () => view('pages.philhealth.eecl-items'))->name('eecl-items');
            Route::get('oecb-packages', fn () => view('pages.philhealth.oecb-packages'))->name('oecb-packages');
        });
        Route::middleware('permission:philhealth.manage')->group(function () {
            Route::get('claims', fn () => view('pages.philhealth.claims'))->name('claims');
            Route::get('batches', fn () => view('pages.philhealth.batches'))->name('batches');
            Route::get('claims/{claim}/esoapdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'icd10Code', 'caseRate', 'items', 'claimBatch']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.esoapdf', compact('claim'));
                return $pdf->download('esoapdf-' . $claim->id . '.pdf');
            })->name('claims.esoapdf');
            Route::get('claims/{claim}/cf5pdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'icd10Code', 'caseRate', 'items', 'claimBatch', 'cf5ShadowBilling']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.cf5pdf', compact('claim'));
                return $pdf->download('cf5-' . $claim->id . '.pdf');
            })->name('claims.cf5pdf');
            Route::get('claims/{claim}/cf1pdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'attendingPhysician', 'icd10Code', 'secondaryIcd10Code', 'tertiaryIcd10Code', 'caseRate', 'items', 'claimBatch']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.cf1pdf', compact('claim'));
                return $pdf->download('cf1-' . $claim->id . '.pdf');
            })->name('claims.cf1pdf');
            Route::get('claims/{claim}/cf2pdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'attendingPhysician', 'referringPhysician', 'icd10Code', 'secondaryIcd10Code', 'tertiaryIcd10Code', 'caseRate', 'items', 'claimBatch']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.cf2pdf', compact('claim'));
                return $pdf->download('cf2-' . $claim->id . '.pdf');
            })->name('claims.cf2pdf');
            Route::get('claims/{claim}/cf3pdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'attendingPhysician', 'icd10Code', 'deliveryType', 'items', 'claimBatch']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.cf3pdf', compact('claim'));
                return $pdf->download('cf3-' . $claim->id . '.pdf');
            })->name('claims.cf3pdf');
            Route::get('claims/{claim}/cf4pdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'attendingPhysician', 'referringPhysician', 'icd10Code', 'secondaryIcd10Code', 'tertiaryIcd10Code', 'caseRate', 'items', 'claimBatch']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.cf4pdf', compact('claim'));
                return $pdf->download('cf4-' . $claim->id . '.pdf');
            })->name('claims.cf4pdf');
            Route::get('claims/{claim}/eeclpdf', function (\App\Models\PhilhealthClaim $claim) {
                $claim->loadMissing(['patient', 'philhealthMember', 'attendingPhysician', 'icd10Code', 'caseRate', 'items', 'claimBatch', 'erAdmission.triageRecord.acuityLevel', 'erAdmission.oecbEntries']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.philhealth.eeclpdf', compact('claim'));
                return $pdf->download('eecl-' . $claim->id . '.pdf');
            })->name('claims.eeclpdf');
        });
        Route::get('reports', fn () => view('pages.philhealth.reports'))->middleware('permission:reports.view')->name('reports');
    });

    // ── Medical Records Routes ─────────────────────────────────────────────────
    Route::get('/medical-records', fn () => view('pages.medical-records'))->middleware('permission:medical-records.view')->name('medical-records');

    Route::get('/medical-records/certificate/{type}/{encounterType}/{encounterId}/{patientId}', [\App\Http\Controllers\PdfReportController::class, 'medicalRecordsCertificate'])->middleware('permission:medical-records.view')->name('medical-records.certificate');
