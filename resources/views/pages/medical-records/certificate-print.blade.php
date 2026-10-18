<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $certType }} - {{ $patient->full_name ?? 'Patient' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .cert-border { border: 2px solid #1e40af; }
        .cert-header-bg { background: linear-gradient(135deg, #1e40af, #3730a3); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto py-4 px-4 no-print">
        <button onclick="window.print()" class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 text-sm font-medium">
            Print / Save as PDF
        </button>
        <a href="#" onclick="window.close(); return false;" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
            Close
        </a>
    </div>

    <div class="max-w-4xl mx-auto bg-white shadow-lg mb-8 print:shadow-none print:mx-0 print:max-w-full">
        <!-- Header -->
        <div class="cert-header-bg text-white px-8 py-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight">SITHEMR DEMO HOSPITAL</h1>
            <p class="text-sm text-blue-200 mt-1">123 Medical Center Drive, Quezon City, Philippines</p>
            <p class="text-sm text-blue-200">Tel: (02) 8123-4567 • Email: info@sithemr.com</p>
            <div class="w-16 h-0.5 bg-white/40 mx-auto mt-3"></div>
            <h2 class="text-xl font-semibold mt-3 uppercase tracking-wide">{{ $certificateTitle }}</h2>
        </div>

        <!-- Body -->
        <div class="px-8 py-6">
            <!-- Patient Info -->
            <div class="border-2 border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Patient Information</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 text-xs">Full Name</span>
                        <p class="font-medium">{{ $patient->last_name }}, {{ $patient->first_name }} {{ $patient->middle_name ?? '' }} {{ $patient->suffix ?? '' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs">Patient Code</span>
                        <p class="font-medium">{{ $patient->patient_code }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs">Date of Birth</span>
                        <p class="font-medium">{{ $patient->date_of_birth?->format('F d, Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs">Age / Gender</span>
                        <p class="font-medium">{{ $patient->date_of_birth?->age ?? '—' }} / {{ $patient->gender?->name ?? '—' }}</p>
                    </div>
                    @if ($patient->philhealth_pin)
                    <div>
                        <span class="text-gray-500 text-xs">PhilHealth PIN</span>
                        <p class="font-medium">{{ $patient->philhealth_pin }}</p>
                    </div>
                    @endif
                    <div>
                        <span class="text-gray-500 text-xs">Contact</span>
                        <p class="font-medium">{{ $patient->contact_no ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs">Address</span>
                        <p class="font-medium">{{ $patient->house_no ?? '' }} {{ $patient->street ?? '' }}, {{ $patient->barangay?->name ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Certificate Content -->
            <div class="mb-6">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">{{ $certificateSubtitle }}</h3>
                <div class="text-sm text-gray-700 leading-relaxed space-y-3">
                    {!! $certificateBody !!}
                </div>
            </div>

            <!-- Encounter Details Table -->
            @if ($encounterData)
            <div class="border-2 border-gray-200 rounded-lg overflow-hidden mb-6">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700">Encounter Details</h4>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm">
                        @foreach ($encounterData as $label => $value)
                        <tr class="border-b border-gray-100 last:border-b-0">
                            <td class="py-2 pr-4 text-gray-500 font-medium w-48">{{ $label }}</td>
                            <td class="py-2 text-gray-900">{{ $value }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            @endif

            <!-- Orders Summary -->
            @if (isset($orders) && count($orders) > 0)
            <div class="border-2 border-gray-200 rounded-lg overflow-hidden mb-6">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700">Services Rendered</h4>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="text-left px-4 py-2">Type</th>
                            <th class="text-left px-4 py-2">Request Code</th>
                            <th class="text-left px-4 py-2">Items</th>
                            <th class="text-right px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2 font-medium">{{ $order['type'] }}</td>
                            <td class="px-4 py-2">{{ $order['code'] }}</td>
                            <td class="px-4 py-2">{{ $order['items'] }}</td>
                            <td class="px-4 py-2 text-right">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $order['status'] === 'Completed' || $order['status'] === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $order['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Footer / Signatures -->
        <div class="px-8 py-6 border-t-2 border-gray-100">
            <div class="grid grid-cols-2 gap-8 text-sm">
                <div class="text-center">
                    <div class="border-t border-gray-300 pt-2 mt-16">
                        <p class="font-medium text-gray-900">{{ $attendingDoctor ?? '________________________' }}</p>
                        <p class="text-xs text-gray-500">Attending Physician</p>
                        <p class="text-xs text-gray-400 mt-1">License No.: ________________</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t border-gray-300 pt-2 mt-16">
                        <p class="font-medium text-gray-900">{{ $hospitalRepresentative ?? '________________________' }}</p>
                        <p class="text-xs text-gray-500">Hospital Representative</p>
                    </div>
                </div>
            </div>
            <p class="text-center text-xs text-gray-400 mt-6">This certificate is issued upon request of the patient/representative for whatever legal purpose it may serve.</p>
            <p class="text-center text-xs text-gray-400">Issued on: {{ now()->format('F d, Y \a\t h:i A') }}</p>
        </div>
    </div>
</body>
</html>
