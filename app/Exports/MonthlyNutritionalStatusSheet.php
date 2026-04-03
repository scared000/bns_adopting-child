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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;

class MonthlyNutritionalStatusSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    private int $dataStartRow = 13;
    private int $dataEndRow   = 13;

    public function __construct(
        public int   $month,
        public int   $year,
        public array $signatories = [],
    ) {}

    public function title(): string { return 'Nutritional Status'; }

    public function columnWidths(): array
    {
        return [
            'A' => 32, 'B' => 5,  'C' => 12, 'D' => 9,  'E' => 12,
            'F' => 20, 'G' => 15, 'H' => 12, 'I' => 9,  'J' => 9,
            'K' => 9,  'L' => 9,  'M' => 15, 'N' => 15,
        ];
    }

    private function sig(string $key, string $fallback = ''): string
    {
        return $this->signatories[$key] ?? $fallback;
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
        $baselineMap = $childIds->isNotEmpty()
            ? OfficeChildVisit::whereIn('adopted_id', $childIds)
                ->whereIn('id', function ($q) use ($childIds) {
                    $q->selectRaw('MIN(id)')
                        ->from('office_child_visits')
                        ->whereIn('adopted_id', $childIds)
                        ->groupBy('adopted_id');
                })
                ->get()
                ->keyBy('adopted_id')
            : collect();

        $baselineDateLabel = $baselineMap->isNotEmpty()
            ? Carbon::parse($baselineMap->min('visit_date'))->format('F d, Y')
            : 'Baseline Date';

        $followUpDateLabel = Carbon::createFromDate($this->year, $this->month)
            ->endOfMonth()->format('F d, Y');

        $blank = array_fill(0, 14, '');
        $rows  = [];

        $rows[] = $blank;
        $rows[] = $blank;

        $rows[] = ['REPUBLIC OF THE PHILIPPINES', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['PROVINCIAL HEALTH OFFICE',    '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Nabunturan, Davao de Oro',     '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = $blank;
        $rows[] = ['NUTRITIONAL STATUS REPORT',                       '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['G-WORKS PARA SA WASTONG NUTRISYON BENEFICIARIES',  '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Monthly Monitoring — ' . $monthName,              '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = $blank;

        $rows[] = [
            'Name of Beneficiaries', '',
            $baselineDateLabel, '', '', '', '',
            $followUpDateLabel, '', '', '', '', '',
            'STATUS',
        ];

        $rows[] = [
            '', 'Sex',
            'Age in months', 'Wt. kg.', 'BL Ht. cm.', 'N.S', 'Date of Weighing',
            'Age in Months', 'WT', 'NS (WFA)', 'HT', 'NS (HFA)', 'WT/FOR H/L-NS',
            '',
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
            $blNS = $baseline ? $this->abbreviateStatus($baseline->status ?? '') : '—';
            $blDate = $baseline ? Carbon::parse($baseline->visit_date)->format('d/m/Y') : '—';

            $fuAgeMonths = $child->birthdate
                ? (int) Carbon::parse($child->birthdate)->diffInMonths(Carbon::parse($visit->visit_date))
                : '—';

            [$wfaNS, $hfaNS, $wfhNS] = $this->parseStatusComponents($visit->status ?? '');

            $dataRows[] = [
                $no++ . '. ' . $child->lastname . ', ' . $child->firstname,
                strtoupper(substr($child->sex ?? '—', 0, 1)),
                $blAgeMonths,
                $baseline?->weight ?? '—',
                $baseline?->height ?? '—',
                $blNS,
                $blDate,
                $fuAgeMonths,
                $visit->weight ?? '—',
                $wfaNS,
                $visit->height ?? '—',
                $hfaNS,
                $wfhNS,
                $this->getRehabStatus($visit->status ?? ''),
            ];
        }

        foreach ($dataRows as $row) {
            $rows[] = $row;
        }

        $this->dataEndRow = count($dataRows) > 0
            ? $this->dataStartRow + count($dataRows) - 1
            : $this->dataStartRow - 1; // signals "no data"

        // Summary
        $total = $followUpVisits->count();
        $rehabilitated = $followUpVisits->filter(
            fn ($v) => $this->getRehabStatus($v->status ?? '') === 'REHABILITATED'
        )->count();
        $maintained = $total - $rehabilitated;
        $rehabRate = $total > 0 ? round(($rehabilitated / $total) * 100, 2) : 0;

        $rows[] = $blank;
        $rows[] = array_merge(array_fill(0, 12, ''), ['REHABILITATED', $rehabilitated.'/'.$total.' ('.$rehabRate.'%)']);
        $rows[] = array_merge(array_fill(0, 12, ''), ['Maintained',    $maintained.'/'.$total.' ('.round(100-$rehabRate,2).'%)']);
        $rows[] = array_merge(array_fill(0, 13, ''), ['100.00%']);
        $rows[] = $blank;

        // Footer
        $rows[] = array_merge(['Prepared by:', '', '', '', 'Noted by:', '', '', '', 'Approved by:'], array_fill(0, 5, ''));
        $rows[] = $blank;
        $rows[] = $blank;
        $rows[] = array_merge(['________________________________','','','','________________________________','','','','________________________________'], array_fill(0, 5, ''));
        $rows[] = array_merge([$this->sig('prepared_by_name','BNS In-Charge'),'','','',$this->sig('noted_by_name','PPO IV/PNAO'),'','','',$this->sig('approved_by_name','PG-Department Head')], array_fill(0, 5, ''));
        $rows[] = array_merge([$this->sig('prepared_by_title','BNS In-Charge'),'','','',$this->sig('noted_by_title','PPO IV/PNAO'),'','','',$this->sig('approved_by_title','PG-Department Head')], array_fill(0, 5, ''));
        $rows[] = $blank; // building image placeholder
        $rows[] = ['Provincial Health Office, Provincial Capitol Complex, Cabidianan, Nabunturan, Davao de Oro','','','','','','','','','','','','',''];
        $rows[] = ['pho@davaodeoro.gov.ph','','','','','','','','','','','','',''];
        return $rows;
    }

    private function abbreviateStatus(string $status): string
    {
        [$wfa, $hfa, $wfh] = $this->parseStatusComponents($status);
        $parts = array_filter([$wfa, $hfa, $wfh], fn ($p) => $p !== '—');
        return $parts ? implode('/', $parts) : ($status ?: '—');
    }

    private function parseStatusComponents(string $status): array
    {
        $map = [
            'Severely Underweight'=>'SUW',
            'Underweight'=>'UW',
            'Normal'=>'N',
            'Overweight'=>'OW',
            'Obese'=>'OB',
            'Severely Stunted'=>'SST',
            'Stunted'=>'ST',
            'Severely Wasted'=>'SW',
            'Wasted'=>'W',
        ];
        $wfa = $hfa = $wfh = '—';
        if (preg_match('/WFA:\s*([^|]+)/i', $status, $m)) $wfa = $map[trim($m[1])] ?? trim($m[1]);
        if (preg_match('/HFA:\s*([^|]+)/i', $status, $m)) $hfa = $map[trim($m[1])] ?? trim($m[1]);
        if (preg_match('/WFH:\s*([^|]+)/i', $status, $m)) $wfh = $map[trim($m[1])] ?? trim($m[1]);
        return [$wfa, $hfa, $wfh];
    }

    private function getRehabStatus(string $status): string
    {
        $s = strtolower($status);
        return (str_contains($s,'normal') && !str_contains($s,'severely') && !str_contains($s,'wasted')
            && !str_contains($s,'obese') && !str_contains($s,'stunted')
            && !str_contains($s,'underweight') && !str_contains($s,'overweight'))
            ? 'REHABILITATED' : 'MAINTAINED';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            3  => [
                'font'=>['bold'=>false,'size'=>12,'name'=>'Times New Roman'],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            4  => [
                'font'=>['bold'=>true, 'size'=>12,'name'=>'Times New Roman'],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            5  => [
                'font'=>['bold'=>false,'name'=>'Times New Roman'],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            7  => [
                'font'=>['bold'=>true,'size'=>14,'name'=>'Times New Roman'],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            8  => [
                'font'=>['bold'=>true,'size'=>11,'name'=>'Times New Roman'],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            9  => [
                'font'=>['bold'=>true,'name'=>'Times New Roman'],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            11 => [
                'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'name'=>'Times New Roman'],
                'fill'=>['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'E97316']],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'wrapText'=>true]],
            12 => [
                'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'name'=>'Times New Roman'],
                'fill'=>['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'E97316']],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'wrapText'=>true]],
        ];
    }

    // ── AfterSheet ───────────────────────────────────────────────────────────

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $sheet->getHighestRow();
                $dataStart = $this->dataStartRow;
                $dataEnd   = $this->dataEndRow;
                $hasData   = $dataEnd >= $dataStart;
                foreach ([3, 4, 5, 7, 8, 9] as $row) {
                    $sheet->mergeCells("A{$row}:N{$row}");
                }
                $sheet->mergeCells('A11:A12');
                $sheet->mergeCells('B11:B12');
                $sheet->mergeCells('C11:G11');
                $sheet->mergeCells('H11:M11');
                $sheet->mergeCells('N11:N12');

                if ($hasData) {
                    // Full table borders
                    $sheet->getStyle("A11:N{$dataEnd}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("G11:G{$dataEnd}")
                        ->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

                    for ($row = $dataStart; $row <= $dataEnd; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(-1);
                        $sheet->getStyle("A{$row}:N{$row}")->getAlignment()->setWrapText(true);
                    }
                    $sheet->getStyle("B{$dataStart}:N{$dataEnd}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    for ($row = $dataStart; $row <= $dataEnd; $row++) {
                        $status = $sheet->getCell("N{$row}")->getValue();
                        if ($status === 'REHABILITATED') {
                            $sheet->getStyle("N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d1fae5');
                            $sheet->getStyle("N{$row}")->getFont()->getColor()->setRGB('065f46');
                        } elseif ($status === 'MAINTAINED') {
                            $sheet->getStyle("N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fef3c7');
                            $sheet->getStyle("N{$row}")->getFont()->getColor()->setRGB('92400e');
                        }
                    }
                } else {
                    $sheet->getStyle('A11:N12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                $sheet->getRowDimension(11)->setRowHeight(30);
                $sheet->getRowDimension(12)->setRowHeight(30);
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $underlineRow = $lastRow - 5;
                $nameRow = $lastRow - 4;
                $titleRow = $lastRow - 3;
                $buildingRow = $lastRow - 2;
                $addrRow = $lastRow - 1;
                $emailRow = $lastRow;
                foreach ([$underlineRow, $nameRow, $titleRow] as $row) {
                    $sheet->mergeCells("A{$row}:D{$row}");
                    $sheet->mergeCells("E{$row}:H{$row}");
                    $sheet->mergeCells("I{$row}:N{$row}");
                }

                $sheet->getStyle("A{$nameRow}:N{$nameRow}")->getFont()->setBold(true);
                foreach (['A', 'E', 'I'] as $col) {
                    $sheet->getStyle("{$col}{$underlineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$col}{$nameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$col}{$titleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Building image
                $sheet->getRowDimension($buildingRow)->setRowHeight(70);
                $sheet->mergeCells("A{$buildingRow}:N{$buildingRow}");

                // Address
                $sheet->mergeCells("A{$addrRow}:N{$addrRow}");
                $sheet->getStyle("A{$addrRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$addrRow}")->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('92400e');

                // Email
                $sheet->mergeCells("A{$emailRow}:N{$emailRow}");
                $sheet->getStyle("A{$emailRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$emailRow}")->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('92400e');

                // PHO Building image
                $buildingPath = public_path('storage/sheet-logo/ddo-building-image.png');
                if (file_exists($buildingPath)) {
                    $d = new Drawing();
                    $d->setName('PHO Building')
                        ->setDescription('Provincial Health Office Building')
                        ->setPath($buildingPath)
                        ->setHeight(110)
                        ->setCoordinates('A' . $buildingRow)
                        ->setOffsetX(215)
                        ->setOffsetY(5)
                        ->setWorksheet($sheet);
                }

                // Logo row
                $sheet->getRowDimension(1)->setRowHeight(65);
                $sheet->getRowDimension(2)->setRowHeight(8);

                $ddoLogoPath = public_path('storage/sheet-logo/ddo-logo.png');
                if (file_exists($ddoLogoPath)) {
                    $d = new Drawing();
                    $d->setName('DDO Logo')
                        ->setDescription('Province of Davao de Oro')
                        ->setPath($ddoLogoPath)
                        ->setHeight(80)
                        ->setCoordinates('E2')
                        ->setOffsetX(8)
                        ->setOffsetY(5)
                        ->setWorksheet($sheet);
                }

                $bpLogoPath = public_path('storage/sheet-logo/sheet-bagong-pilipinas_logo.png');
                if (file_exists($bpLogoPath)) {
                    $d = new Drawing();
                    $d->setName('Bagong Pilipinas')
                        ->setDescription('Bagong Pilipinas')
                        ->setPath($bpLogoPath)
                        ->setHeight(80)
                        ->setCoordinates('H2')
                        ->setOffsetX(50)
                        ->setOffsetY(5)
                        ->setWorksheet($sheet);
                }
            },
        ];
    }
}
