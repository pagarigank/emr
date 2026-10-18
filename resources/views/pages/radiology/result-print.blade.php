<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radiology Result - {{ $item->exam_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; padding: 40px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #000; }
        .header h1 { font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #333; }
        .header .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin-bottom: 20px; padding: 12px; border: 1px solid #ccc; background: #f9f9f9; }
        .info-grid .label { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #555; }
        .info-grid .value { font-size: 12px; margin-bottom: 6px; }
        .section { margin-bottom: 20px; }
        .section h3 { font-size: 11px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 8px; color: #333; }
        .section .content { white-space: pre-wrap; font-size: 12px; line-height: 1.7; padding: 8px 0; }
        .findings-box, .impression-box { padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .findings-box { background: #f5f5f5; border-left: 3px solid #888; }
        .impression-box { background: #f0f4ff; border-left: 3px solid #4466cc; }
        .signature-section { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .signature-box { text-align: center; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 40px; padding-top: 6px; }
        .signature-box .label { font-size: 9px; text-transform: uppercase; color: #666; }
        .signature-box .name { font-size: 11px; font-weight: bold; }
        .disclaimer { margin-top: 30px; font-size: 9px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 12px; }
        .images-section { margin-top: 20px; }
        .images-section img { max-width: 100%; max-height: 300px; border: 1px solid #ccc; margin: 8px 0; }
        @media print { body { padding: 20px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Radiology Report</h1>
        <p>Digital Radiography / Diagnostic Imaging Services</p>
        <div class="report-title">{{ $item->exam_name }} @if($item->modality_name)({{ $item->modality_name }})@endif</div>
    </div>

    <div class="info-grid">
        <div>
            <div class="label">Patient Name</div>
            <div class="value">{{ $item->patient_name ?? 'N/A' }}</div>
        </div>
        <div>
            <div class="label">Request Code</div>
            <div class="value">{{ $item->request_code ?? 'N/A' }}</div>
        </div>
        <div>
            <div class="label">Exam Date</div>
            <div class="value">{{ $item->performed_at ? $item->performed_at->format('M d, Y h:i A') : ($item->result_date ? $item->result_date->format('M d, Y h:i A') : 'N/A') }}</div>
        </div>
        <div>
            <div class="label">Report Date</div>
            <div class="value">{{ $item->result_date ? $item->result_date->format('M d, Y h:i A') : 'N/A' }}</div>
        </div>
        @if($item->radiologist)
        <div>
            <div class="label">Radiologist</div>
            <div class="value">{{ $item->radiologist->full_name }}</div>
        </div>
        @endif
        @if($item->performedBy)
        <div>
            <div class="label">Performed By</div>
            <div class="value">{{ $item->performedBy->full_name }}</div>
        </div>
        @endif
    </div>

    @if($item->technique)
    <div class="section">
        <h3>Technique</h3>
        <div class="content">{{ $item->technique }}</div>
    </div>
    @endif

    @if($item->findings)
    <div class="section">
        <h3>Radiologic Findings</h3>
        <div class="findings-box"><div class="content">{{ $item->findings }}</div></div>
    </div>
    @endif

    @if($item->impression)
    <div class="section">
        <h3>Impression / Diagnosis</h3>
        <div class="impression-box"><div class="content">{{ $item->impression }}</div></div>
    </div>
    @endif

    @if($item->result_text)
    <div class="section">
        <h3>Additional Notes / Full Report</h3>
        <div class="content">{{ $item->result_text }}</div>
    </div>
    @endif

    @if($item->doctor_notes)
    <div class="section">
        <h3>Clinical Remarks</h3>
        <div class="content"><em>{{ $item->doctor_notes }}</em></div>
    </div>
    @endif

    @php $images = $item->images ?? collect([]); @endphp
    @if($images->count() > 0)
    <div class="images-section section">
        <h3>Attached Images ({{ $images->count() }})</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            @foreach($images as $img)
                @php $isImg = str_starts_with($img->mime_type ?? '', 'image/'); @endphp
                @if($isImg)
                <div style="text-align: center;">
                    <img src="{{ public_path('storage/' . $img->path) }}" alt="{{ $img->original_name }}" style="max-width: 100%; max-height: 200px;">
                    <div style="font-size: 9px; color: #666;">{{ $img->original_name }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="line"></div>
            <div class="label">Performed By</div>
            <div class="name">{{ $item->performedBy?->full_name ?? '____________________' }}</div>
            <div style="font-size: 9px; color: #888;">Radiologic Technologist</div>
        </div>
        <div class="signature-box">
            <div class="line"></div>
            <div class="label">Read By</div>
            <div class="name">{{ $item->radiologist?->full_name ?? '____________________' }}</div>
            <div style="font-size: 9px; color: #888;">Radiologist</div>
        </div>
        <div class="signature-box">
            <div class="line"></div>
            <div class="label">Date Reported</div>
            <div class="name">{{ $item->result_date ? $item->result_date->format('M d, Y') : '____________________' }}</div>
        </div>
    </div>

    <div class="disclaimer">
        This report is electronically generated and is valid without a physical signature. <br>
        DOH AO 2021-0037 compliant &bull; PCHRD / PRS standards
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 24px; background: #4466cc; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
        <button onclick="window.close()" style="padding: 8px 24px; background: #888; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">Close</button>
    </div>
</body>
</html>
