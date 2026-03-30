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
use Maatwebsite\Excel\Events\AfterSheet;

class MonthlyVisitItemsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(
        public int $month,
        public int $year
    ) {}

    public function title(): string
    {
        return 'Items Distributed';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 30, 'C' => 30,
            'D' => 35, 'E' => 12, 'F' => 15,
        ];
    }

    public function array(): array
    {
        $monthName = Carbon::createFromDate($this->year, $this->month)->format('F Y');

        $items = VisitItems::with(['officeVisit.child', 'officeVisit.bns'])
            ->whereHas('officeVisit', function ($q) {
                $q->whereMonth('visit_date', $this->month)
                    ->whereYear('visit_date', $this->year);
            })
            ->get();

        $rows = [];

        $rows[] = ['REPUBLIC OF THE PHILIPPINES', '', '', '', '', ''];
        $rows[] = ['PROVINCIAL HEALTH OFFICE', '', '', '', '', ''];
        $rows[] = ['Nabunturan, Davao de Oro', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['DISTRIBUTION LIST', '', '', '', '', ''];
        $rows[] = ['G-WORKS PARA SA WASTONG NUTRISYON BENEFICIARIES', '', '', '', '', ''];
        $rows[] = ['Month: ' . $monthName, '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        $rows[] = ['VISIT DATE', 'NAME OF RECIPIENT', 'ADDRESS', 'ITEM DESCRIPTION', 'QUANTITY', 'AMOUNT (₱)'];

        $no = 1;
        foreach ($items as $item) {
            $visit = $item->officeVisit;
            $child = $visit?->child;
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
                $item->item_quantity ?? '—',
                $item->item_amount ? number_format($item->item_amount, 2) : 'Free',
            ];
        }

        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['Prepared by:', '', 'Noted by:', '', 'Approved by:', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['________________________________', '', '________________________________', '', '________________________________', ''];
        $rows[] = ['BNS In-Charge', '', 'PPO IV/PNAO', '', 'PG-Department Head', ''];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            6 => ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            9 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E97316']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A5:F5');
                $sheet->mergeCells('A6:F6');
                $sheet->mergeCells('A7:F7');

                $sheet->getStyle('A9:F' . ($lastRow - 6))
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->freezePane('A10');
            },
        ];
    }
}
