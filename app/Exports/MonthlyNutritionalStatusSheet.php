<?php

namespace App\Exports;

use App\Models\OfficeChildVisit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Nutritional Status sheet — dual-date layout
 *
 * Columns (A–N, 14 total):
 *   A  = Name of Beneficiaries
 *   B  = Sex
 *   --- BASELINE GROUP (C–G) ---
 *   C  = Age in months (at baseline visit)
 *   D  = Wt. kg.       (baseline)
 *   E  = BL Ht. cm.    (baseline)
 *   F  = N.S           (baseline abbreviated status: SUW/SST …)
 *   G  = Date of Weighing (baseline)
 *   --- FOLLOW-UP GROUP (H–M) ---
 *   H  = Age in Months (at follow-up visit)
 *   I  = WT            (follow-up weight)
 *   J  = NS (WFA)      (weight-for-age abbreviated)
 *   K  = HT            (follow-up height)
 *   L  = NS (HFA)      (height-for-age abbreviated)
 *   M  = WT/FOR H/L-NS (weight-for-height abbreviated)
 *   N  = STATUS        (REHABILITATED / MAINTAINED)
 */
class MonthlyNutritionalStatusSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    /** Shared between array() and registerEvents() */
    private int $dataStartRow = 13;
    private int $dataEndRow   = 13;

    public function __construct(
        public int $month,
        public int $year
    ) {}

    public function title(): string
    {
        return 'Nutritional Status';
    }


    public function columnWidths(): array
    {
        return [
            'A' => 32, // Name
            'B' => 5,  // Sex
            'C' => 12, // Age mos (baseline)
            'D' => 9,  // Wt kg (baseline)
            'E' => 12, // BL Ht cm (baseline)
            'F' => 20, // N.S (baseline)
            'G' => 15, // Date (baseline)
            'H' => 12, // Age mos (follow-up)
            'I' => 9,  // WT (follow-up)
            'J' => 9,  // NS WFA (follow-up)
            'K' => 9,  // HT (follow-up)
            'L' => 9,  // NS HFA (follow-up)
            'M' => 15, // WT/FOR H/L-NS
            'N' => 15, // STATUS
        ];
    }


    public function array(): array
    {
        $monthName = Carbon::createFromDate($this->year, $this->month)->format('F Y');
        $followUpVisits = OfficeChildVisit::with(['child', 'bns', 'office'])
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('office_child_visits')
                    ->whereMonth('visit_date', $this->month)
                    ->whereYear('visit_date', $this->year)
                    ->groupBy('adopted_id');
            })
            ->orderBy('visit_date')
            ->get();

        $childIds = $followUpVisits->pluck('adopted_id')->filter()->unique();

        $baselineMap = OfficeChildVisit::whereIn('adopted_id', $childIds)
            ->whereIn('id', function ($q) use ($childIds) {
                $q->selectRaw('MIN(id)')
                    ->from('office_child_visits')
                    ->whereIn('adopted_id', $childIds)
                    ->groupBy('adopted_id');
            })
            ->get()
            ->keyBy('adopted_id');

        $baselineDateLabel = $baselineMap->isNotEmpty()
            ? Carbon::parse($baselineMap->min('visit_date'))->format('F d, Y')
            : 'Baseline Date';

        $followUpDateLabel = Carbon::createFromDate($this->year, $this->month)
            ->endOfMonth()
            ->format('F d, Y');


        $blank = array_fill(0, 14, '');
        $rows  = [];
        $rows[] = $blank;
        $rows[] = $blank;
        $rows[] = ['REPUBLIC OF THE PHILIPPINES', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['PROVINCIAL HEALTH OFFICE',    '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Nabunturan, Davao de Oro',     '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = $blank;
        $rows[] = ['NUTRITIONAL STATUS REPORT',                      '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['G-WORKS PARA SA WASTONG NUTRISYON BENEFICIARIES','', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Monthly Monitoring — ' . $monthName,            '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = $blank;
        $rows[] = [
            'Name of Beneficiaries', '',       // A–B
            $baselineDateLabel, '', '', '', '', // C–G  (baseline group)
            $followUpDateLabel, '', '', '', '', '', // H–M  (follow-up group)
            'STATUS',                          // N
        ];

        // Row 12 — sub-column header
        $rows[] = [
            '', 'Sex',                                                          // A, B
            'Age in months', 'Wt. kg.', 'BL Ht. cm.', 'N.S', 'Date of Weighing', // C-G
            'Age in Months', 'WT', 'NS (WFA)', 'HT', 'NS (HFA)', 'WT/FOR H/L-NS', // H-M
            '',  // N
        ];

        $this->dataStartRow = 13;
        $dataRows = [];
        $no = 1;

        foreach ($followUpVisits as $visit) {
            $child = $visit->child;
            if (!$child) continue;

            $baseline = $baselineMap->get($child->id);
            $blAgeMonths = ($child->birthdate && $baseline)
                ? (int) Carbon::parse($child->birthdate)->diffInMonths(Carbon::parse($baseline->visit_date))
                : '—';
            $blNS   = $baseline ? $this->abbreviateStatus($baseline->status ?? '') : '—';
            $blDate = $baseline ? Carbon::parse($baseline->visit_date)->format('d/m/Y') : '—';

            $fuAgeMonths = $child->birthdate
                ? (int) Carbon::parse($child->birthdate)->diffInMonths(Carbon::parse($visit->visit_date))
                : '—';

            [$wfaNS, $hfaNS, $wfhNS] = $this->parseStatusComponents($visit->status ?? '');
            $statusLabel = $this->getRehabStatus($visit->status ?? '');

            $dataRows[] = [
                $no++ . '. ' . $child->lastname . ', ' . $child->firstname,  // A
                strtoupper(substr($child->sex ?? '—', 0, 1)), // B
                $blAgeMonths, // C
                $baseline?->weight ?? '—', // D
                $baseline?->height ?? '—', // E
                $blNS,                    // F
                $blDate,                  // G
                $fuAgeMonths,             // H
                $visit->weight ?? '—',    // I
                $wfaNS,                   // J
                $visit->height ?? '—',    // K
                $hfaNS,                   // L
                $wfhNS,                   // M
                $statusLabel,             // N
            ];
        }

        foreach ($dataRows as $row) {
            $rows[] = $row;
        }

        $this->dataEndRow = $this->dataStartRow + count($dataRows) - 1;
        $total = $followUpVisits->count();
        $rehabilitated = $followUpVisits->filter(fn($v) => $this->getRehabStatus($v->status ?? '') === 'REHABILITATED')->count();
        $maintained = $total - $rehabilitated;
        $rehabRate = $total > 0 ? round(($rehabilitated / $total) * 100, 2) : 0;

        $rows[] = $blank;
        $rows[] = array_merge(array_fill(0, 12, ''), [
            'REHABILITATED',
            $rehabilitated . '/' . $total . ' (' . $rehabRate . '%)',
        ]);
        $rows[] = array_merge(array_fill(0, 12, ''), [
            'Maintained',
            $maintained . '/' . $total . ' (' . round(100 - $rehabRate, 2) . '%)',
        ]);
        $rows[] = array_merge(array_fill(0, 13, ''), ['100.00%']);
        $rows[] = $blank;

        //Footer
        $rows[] = array_merge(
            ['Prepared by:', '', '', '', 'Noted by:', '', '', '', 'Approved by:'],
            array_fill(0, 5, '')
        );
        $rows[] = $blank;
        $rows[] = $blank;
        $rows[] = array_merge(
            ['________________________________', '', '', '', '________________________________', '', '', '', '________________________________'],
            array_fill(0, 5, '')
        );
        $rows[] = array_merge(
            ['ND-III/PNC', '', '', '', 'PPO IV/PNAO', '', '', '', 'PG-Department Head'],
            array_fill(0, 5, '')
        );

        $rows[] = [
            'Provincial Health Office, Provincial Capitol Complex, Cabidianan, Nabunturan, Davao de Oro',
            '', '', '', '', '', '', '', '', '', '', '', '', '',
        ];

        return $rows;
    }

    /**
     * Combines WFA/HFA/WFH into a slash-separated abbreviated string.
     * E.g. "WFA: Severely Underweight | HFA: Stunted | WFH: Normal" → "SUW/ST/N"
     */
    private function abbreviateStatus(string $status): string
    {
        [$wfa, $hfa, $wfh] = $this->parseStatusComponents($status);
        $parts = array_filter([$wfa, $hfa, $wfh], fn($p) => $p !== '—');

        return $parts ? implode('/', $parts) : ($status ?: '—');
    }

    /**
     * Returns [WFA abbrev, HFA abbrev, WFH abbrev] from a composite status string.
     * Input format: "WFA: Severely Underweight | HFA: Stunted | WFH: Normal"
     */
    private function parseStatusComponents(string $status): array
    {
        $map = [
            'Severely Underweight' => 'SUW',
            'Underweight' => 'UW',
            'Normal' => 'N',
            'Overweight' => 'OW',
            'Obese' => 'OB',
            'Severely Stunted' => 'SST',
            'Stunted' => 'ST',
            'Severely Wasted' => 'SW',
            'Wasted' => 'W',
        ];

        $wfa = $hfa = $wfh = '—';

        if (preg_match('/WFA:\s*([^|]+)/i', $status, $m)) {
            $wfa = $map[trim($m[1])] ?? trim($m[1]);
        }
        if (preg_match('/HFA:\s*([^|]+)/i', $status, $m)) {
            $hfa = $map[trim($m[1])] ?? trim($m[1]);
        }
        if (preg_match('/WFH:\s*([^|]+)/i', $status, $m)) {
            $wfh = $map[trim($m[1])] ?? trim($m[1]);
        }

        return [$wfa, $hfa, $wfh];
    }

    /**
     * A child is REHABILITATED when all three components are "Normal".
     */
    private function getRehabStatus(string $status): string
    {
        $s = strtolower($status);

        $isFullyNormal = str_contains($s, 'normal')
            && !str_contains($s, 'severely')
            && !str_contains($s, 'wasted')
            && !str_contains($s, 'obese')
            && !str_contains($s, 'stunted')
            && !str_contains($s, 'underweight')
            && !str_contains($s, 'overweight');

        return $isFullyNormal ? 'REHABILITATED' : 'MAINTAINED';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            3  => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4  => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5  => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            7  => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            8  => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            9  => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            11 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E97316']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],

            12 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E97316']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $sheet->getHighestRow();
                $dataStart = $this->dataStartRow;
                $dataEnd   = $this->dataEndRow;
                foreach ([3, 4, 5, 7, 8, 9] as $row) {
                    $sheet->mergeCells("A{$row}:N{$row}");
                }

                $sheet->mergeCells('A11:A12');
                $sheet->mergeCells('B11:B12');
                $sheet->mergeCells('C11:G11');
                $sheet->mergeCells('H11:M11');
                $sheet->mergeCells('N11:N12');

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A11:N{$dataEnd}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    $sheet->getStyle("G11:G{$dataEnd}")
                        ->getBorders()->getRight()
                        ->setBorderStyle(Border::BORDER_MEDIUM);
                }

                for ($row = $dataStart; $row <= $dataEnd; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    $sheet->getStyle("A{$row}:N{$row}")->getAlignment()->setWrapText(true);
                }

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("B{$dataStart}:N{$dataEnd}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getStyle("A{$dataStart}:A{$dataEnd}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                for ($row = $dataStart; $row <= $dataEnd; $row++) {
                    $status = $sheet->getCell("N{$row}")->getValue();

                    if ($status === 'REHABILITATED') {
                        $sheet->getStyle("N{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('d1fae5'); // green-100

                        $sheet->getStyle("N{$row}")->getFont()
                            ->getColor()->setRGB('065f46');

                    } elseif ($status === 'MAINTAINED') {
                        $sheet->getStyle("N{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('fef3c7'); // yellow-100

                        $sheet->getStyle("N{$row}")->getFont()
                            ->getColor()->setRGB('92400e');
                    }
                }


                $sheet->getRowDimension(11)->setRowHeight(30);
                $sheet->getRowDimension(12)->setRowHeight(30);
                $sheet->freezePane("A" . $dataStart);
                $sheet->mergeCells("A{$lastRow}:N{$lastRow}");
                $sheet->getStyle("A{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$lastRow}")
                    ->getFont()->setItalic(true)->setSize(8);
            },
        ];
    }
}
