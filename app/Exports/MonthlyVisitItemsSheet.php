<?php

namespace App\Exports;

use App\Models\VisitItems;
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

class MonthlyVisitItemsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(
        public int   $month,
        public int   $year,
        public array $signatories = [],
    ) {}

    public function title(): string { return 'Items Distributed'; }

    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 30, 'C' => 30,
            'D' => 35, 'E' => 12, 'F' => 15,
        ];
    }

    private function sig(string $key, string $fallback = ''): string
    {
        return $this->signatories[$key] ?? $fallback;
    }

    public function array(): array
    {
        $monthName = Carbon::createFromDate($this->year, $this->month)->format('F Y');

        $items = VisitItems::with(['officeVisit.child', 'officeVisit.bns'])
            ->whereHas('officeVisit', function ($q) {
                $q->whereMonth('visit_date', $this->month)
                    ->whereYear('visit_date',  $this->year);
            })
            ->get();

        $rows = [];

        // Rows 1–2 : reserved for logos
        $rows[] = ['', '', '', '', '', ''];  // row 1
        $rows[] = ['', '', '', '', '', ''];  // row 2

        // Rows 3–10 : title block
        $rows[] = ['REPUBLIC OF THE PHILIPPINES', '', '', '', '', ''];
        $rows[] = ['PROVINCIAL HEALTH OFFICE',    '', '', '', '', ''];
        $rows[] = ['Nabunturan, Davao de Oro',     '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['DISTRIBUTION LIST',                                   '', '', '', '', ''];
        $rows[] = ['G-WORKS PARA SA WASTONG NUTRISYON BENEFICIARIES',     '', '', '', '', ''];
        $rows[] = ['Month: ' . $monthName,                                '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Row 11 : table header
        $rows[] = ['VISIT DATE', 'NAME OF RECIPIENT', 'ADDRESS', 'ITEM DESCRIPTION', 'QUANTITY', 'AMOUNT (₱)'];

        // Data rows
        $no = 1;
        foreach ($items as $item) {
            $visit   = $item->officeVisit;
            $child   = $visit?->child;
            $address = collect([
                $child?->purok ? 'Purok ' . $child->purok : null,
                $child?->barangay?->brgyDesc,
                $child?->municipality?->citymunDesc,
            ])->filter()->implode(', ');

            $rows[] = [
                $visit?->visit_date ? Carbon::parse($visit->visit_date)->format('M d, Y') : '—',
                $no++ . '. ' . ($child ? $child->lastname . ', ' . $child->firstname : '—'),
                $address ?: '—',
                $item->Item_description ?? '—',
                $item->item_quantity    ?? '—',
                $item->item_amount      ? number_format($item->item_amount, 2) : 'Free',
            ];
        }

        // Footer
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['Prepared by:', '', 'Noted by:', '', 'Approved by:', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['________________________________', '', '________________________________', '', '________________________________', ''];
        $rows[] = [
            $this->sig('prepared_by_name',  'BNS In-Charge'),        '',
            $this->sig('noted_by_name',     'PPO IV/PNAO'),          '',
            $this->sig('approved_by_name',  'PG-Department Head'),   '',
        ];
        $rows[] = [
            $this->sig('prepared_by_title', 'BNS In-Charge'),        '',
            $this->sig('noted_by_title',    'PPO IV/PNAO'),          '',
            $this->sig('approved_by_title', 'PG-Department Head'),   '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            3  => ['font'=>['bold'=>true,'size'=>12],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            4  => ['font'=>['bold'=>true,'size'=>12],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            5  => ['font'=>['bold'=>true],           'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            7  => ['font'=>['bold'=>true,'size'=>14],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            8  => ['font'=>['bold'=>true,'size'=>11],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            9  => ['font'=>['bold'=>true],           'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            11 => [
                'font'      => ['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
                'fill'      => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'E97316']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Merge title rows
                foreach ([3, 4, 5, 7, 8, 9] as $row) {
                    $sheet->mergeCells("A{$row}:F{$row}");
                }

                // Table borders: header (row 11) through last data row
                // Footer starts at: lastRow - 7 (2 blank + Prepared by + 2 blank + underline + name + title)
                $dataLastRow = $lastRow - 8;
                if ($dataLastRow >= 11) {
                    $sheet->getStyle("A11:F{$dataLastRow}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                } else {
                    // Header-only borders when no data
                    $sheet->getStyle('A11:F11')
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // ── NO freeze pane ──

                // Footer signatory styling
                $nameRow  = $lastRow - 1;
                $titleRow = $lastRow;
                $sheet->getStyle("A{$nameRow}:F{$nameRow}")->getFont()->setBold(true);
                foreach (['A', 'C', 'E'] as $col) {
                    $sheet->getStyle("{$col}{$nameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$col}{$titleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Logo row heights
                $sheet->getRowDimension(1)->setRowHeight(65);
                $sheet->getRowDimension(2)->setRowHeight(8);

                // DDO logo — far left (A1)
                $ddoLogoPath = public_path('storage/sheet-logo/ddo-logo.png');
                if (file_exists($ddoLogoPath)) {
                    $d = new Drawing();
                    $d->setName('DDO Logo')->setDescription('Province of Davao de Oro')
                        ->setPath($ddoLogoPath)->setHeight(80)
                        ->setCoordinates('A1')->setOffsetX(8)->setOffsetY(5)
                        ->setWorksheet($sheet);
                }

                $bpLogoPath = public_path('storage/sheet-logo/sheet-bagong-pilipinas_logo.png');
                if (file_exists($bpLogoPath)) {
                    $d = new Drawing();
                    $d->setName('Bagong Pilipinas')->setDescription('Bagong Pilipinas')
                        ->setPath($bpLogoPath)->setHeight(80)
                        ->setCoordinates('F1')->setOffsetX(8)->setOffsetY(5)
                        ->setWorksheet($sheet);
                }
            },
        ];
    }
}
