<?php

namespace App\Livewire\Philhealth;

use App\Models\ClaimBatch;
use App\Models\PhilhealthClaim;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportsCrud extends Component
{
    public string $date_from = '', $date_to = '';
    public array $summary = [], $monthly = [], $statusBreakdown = [], $aging = [];

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
        $this->loadReports();
    }

    public function updatedDateFrom(): void { $this->loadReports(); }
    public function updatedDateTo(): void { $this->loadReports(); }

    public function loadReports(): void
    {
        $this->loadSummary();
        $this->loadMonthly();
        $this->loadStatusBreakdown();
        $this->loadAging();
    }

    protected function loadSummary(): void
    {
        $q = PhilhealthClaim::where('history', 1);
        if ($this->date_from) $q->whereDate('created_date', '>=', $this->date_from);
        if ($this->date_to) $q->whereDate('created_date', '<=', $this->date_to);

        $totalClaims = (clone $q)->count();
        $totalClaimed = (clone $q)->sum('total_claimed_amount');
        $totalPaid = (clone $q)->where('status', 'Paid')->sum('voucher_amount');
        $totalActual = (clone $q)->sum('total_actual_amount');

        $submitted = (clone $q)->whereIn('status', ['Submitted', 'Acknowledged'])->count();
        $acknowledged = (clone $q)->where('status', 'Acknowledged')->count();
        $paid = (clone $q)->where('status', 'Paid')->count();
        $denied = (clone $q)->where('status', 'Denied')->count();

        $this->summary = [
            'total_claims' => $totalClaims,
            'total_claimed' => $totalClaimed,
            'total_paid' => $totalPaid,
            'total_actual' => $totalActual,
            'submitted' => $submitted,
            'acknowledged' => $acknowledged,
            'paid' => $paid,
            'denied' => $denied,
            'approval_rate' => $totalClaims > 0 ? round(($paid + $acknowledged) / $totalClaims * 100, 1) : 0,
            'collection_rate' => $totalClaimed > 0 ? round($totalPaid / $totalClaimed * 100, 1) : 0,
        ];
    }

    protected function loadMonthly(): void
    {
        $raw = PhilhealthClaim::where('history', 1)
            ->whereNotNull('created_date')
            ->where('created_date', '>=', now()->subMonths(12))
            ->get()
            ->groupBy(fn ($c) => $c->created_date->format('Y-m'))
            ->map(fn ($group) => [
                'month' => $group->first()->created_date->format('M Y'),
                'claims' => $group->count(),
                'claimed' => $group->sum('total_claimed_amount'),
                'paid' => $group->where('status', 'Paid')->sum('voucher_amount'),
                'actual' => $group->sum('total_actual_amount'),
            ])
            ->values()
            ->toArray();

        $this->monthly = $raw;
    }

    protected function loadStatusBreakdown(): void
    {
        $this->statusBreakdown = PhilhealthClaim::where('history', 1)
            ->selectRaw("status, COUNT(*) as count, SUM(total_claimed_amount) as total_amount")
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    protected function loadAging(): void
    {
        $now = now();

        $buckets = [
            '0-30 days' => fn ($q) => $q->whereRaw('DATEDIFF(day, created_date, ?) BETWEEN 0 AND 30', [$now]),
            '31-60 days' => fn ($q) => $q->whereRaw('DATEDIFF(day, created_date, ?) BETWEEN 31 AND 60', [$now]),
            '61-90 days' => fn ($q) => $q->whereRaw('DATEDIFF(day, created_date, ?) BETWEEN 61 AND 90', [$now]),
            '90+ days' => fn ($q) => $q->whereRaw('DATEDIFF(day, created_date, ?) > 90', [$now]),
        ];

        $this->aging = [];
        foreach ($buckets as $label => $scope) {
            $q = PhilhealthClaim::where('history', 1)->whereIn('status', ['Submitted', 'Acknowledged']);
            $scope($q);
            $this->aging[] = [
                'bucket' => $label,
                'count' => (clone $q)->count(),
                'amount' => (clone $q)->sum('total_claimed_amount'),
            ];
        }
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->loadReports();

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['PhilHealth e-Claims Report']);
            fputcsv($handle, ['Date Range', $this->date_from, 'to', $this->date_to]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Claims', $this->summary['total_claims'] ?? 0]);
            fputcsv($handle, ['Total Claimed', ($this->summary['total_claimed'] ?? 0)]);
            fputcsv($handle, ['Total Paid', ($this->summary['total_paid'] ?? 0)]);
            fputcsv($handle, ['Total Actual', ($this->summary['total_actual'] ?? 0)]);
            fputcsv($handle, ['Approval Rate', ($this->summary['approval_rate'] ?? 0) . '%']);
            fputcsv($handle, ['Collection Rate', ($this->summary['collection_rate'] ?? 0) . '%']);
            fputcsv($handle, []);

            fputcsv($handle, ['Status Breakdown']);
            fputcsv($handle, ['Status', 'Count', 'Total Amount']);
            foreach ($this->statusBreakdown as $sb) {
                fputcsv($handle, [$sb['status'], $sb['count'], $sb['total_amount']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['Aging (Pending Claims)']);
            fputcsv($handle, ['Bucket', 'Claims', 'Amount']);
            foreach ($this->aging as $a) {
                fputcsv($handle, [$a['bucket'], $a['count'], $a['amount']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['Monthly Trend (Last 12 Months)']);
            fputcsv($handle, ['Month', 'Claims', 'Actual Amount', 'Claimed Amount', 'Paid Amount', 'Collection %']);
            foreach ($this->monthly as $m) {
                $colPct = $m['claimed'] > 0 ? round($m['paid'] / $m['claimed'] * 100, 1) . '%' : '0%';
                fputcsv($handle, [$m['month'], $m['claims'], $m['actual'], $m['claimed'], $m['paid'], $colPct]);
            }

            fclose($handle);
        };

        $filename = 'philhealth-reports-' . now()->format('Y-m-d') . '.csv';
        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->loadReports();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PhilHealth Report');

        $bold = ['font' => ['bold' => true]];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
        ];
        $r = 1;

        // Title
        $sheet->setCellValue("A$r", 'PhilHealth e-Claims Report');
        $sheet->getStyle("A$r")->applyFromArray($bold);
        $r += 2;

        // Date range
        $sheet->setCellValue("A$r", 'Date Range:');
        $sheet->setCellValue("B$r", $this->date_from . ' to ' . $this->date_to);
        $r += 2;

        // Summary
        $sheet->setCellValue("A$r", 'Summary');
        $sheet->getStyle("A$r")->applyFromArray($bold);
        $r++;
        $sheet->setCellValue("A$r", 'Metric');
        $sheet->setCellValue("B$r", 'Value');
        $sheet->getStyle("A$r:B$r")->applyFromArray($headerStyle);
        $r++;
        foreach ([
            'Total Claims' => $this->summary['total_claims'] ?? 0,
            'Total Claimed' => ($this->summary['total_claimed'] ?? 0),
            'Total Paid' => ($this->summary['total_paid'] ?? 0),
            'Total Actual' => ($this->summary['total_actual'] ?? 0),
            'Approval Rate' => ($this->summary['approval_rate'] ?? 0) . '%',
            'Collection Rate' => ($this->summary['collection_rate'] ?? 0) . '%',
        ] as $label => $val) {
            $sheet->setCellValue("A$r", $label);
            $sheet->setCellValue("B$r", $val);
            $r++;
        }
        $r += 2;

        // Status Breakdown
        $sheet->setCellValue("A$r", 'Status Breakdown');
        $sheet->getStyle("A$r")->applyFromArray($bold);
        $r++;
        $sheet->setCellValue("A$r", 'Status');
        $sheet->setCellValue("B$r", 'Count');
        $sheet->setCellValue("C$r", 'Total Amount');
        $sheet->getStyle("A$r:C$r")->applyFromArray($headerStyle);
        $r++;
        foreach ($this->statusBreakdown as $sb) {
            $sheet->setCellValue("A$r", $sb['status']);
            $sheet->setCellValue("B$r", $sb['count']);
            $sheet->setCellValue("C$r", $sb['total_amount']);
            $r++;
        }
        $r += 2;

        // Aging
        $sheet->setCellValue("A$r", 'Aging (Pending Claims)');
        $sheet->getStyle("A$r")->applyFromArray($bold);
        $r++;
        $sheet->setCellValue("A$r", 'Bucket');
        $sheet->setCellValue("B$r", 'Claims');
        $sheet->setCellValue("C$r", 'Amount');
        $sheet->getStyle("A$r:C$r")->applyFromArray($headerStyle);
        $r++;
        foreach ($this->aging as $a) {
            $sheet->setCellValue("A$r", $a['bucket']);
            $sheet->setCellValue("B$r", $a['count']);
            $sheet->setCellValue("C$r", $a['amount']);
            $r++;
        }
        $r += 2;

        // Monthly Trend
        $sheet->setCellValue("A$r", 'Monthly Trend (Last 12 Months)');
        $sheet->getStyle("A$r")->applyFromArray($bold);
        $r++;
        $sheet->setCellValue("A$r", 'Month');
        $sheet->setCellValue("B$r", 'Claims');
        $sheet->setCellValue("C$r", 'Actual');
        $sheet->setCellValue("D$r", 'Claimed');
        $sheet->setCellValue("E$r", 'Paid');
        $sheet->setCellValue("F$r", 'Collection %');
        $sheet->getStyle("A$r:F$r")->applyFromArray($headerStyle);
        $r++;
        foreach ($this->monthly as $m) {
            $pct = $m['claimed'] > 0 ? round($m['paid'] / $m['claimed'] * 100, 1) . '%' : '0%';
            $sheet->setCellValue("A$r", $m['month']);
            $sheet->setCellValue("B$r", $m['claims']);
            $sheet->setCellValue("C$r", $m['actual']);
            $sheet->setCellValue("D$r", $m['claimed']);
            $sheet->setCellValue("E$r", $m['paid']);
            $sheet->setCellValue("F$r", $pct);
            $r++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'philhealth-reports-' . now()->format('Y-m-d') . '.xlsx';
        $path = storage_path("app/temp/$filename");
        $realPath = realpath(dirname($path)) ?: dirname($path);
        if (!str_starts_with($realPath, realpath(storage_path())) ?: storage_path()) {
            abort(400, 'Invalid file path.');
        }
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.philhealth.reports-crud');
    }
}
