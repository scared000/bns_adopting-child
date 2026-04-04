<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyMonitoringExport implements WithMultipleSheets
{
    public function __construct(
        public int   $month,
        public int   $year,
        public array $signatories = [],
        public string $municipality = '',
        public string $batch = '',
    ) {}

    public function sheets(): array
    {
        return [
            new MonthlyNutritionalStatusSheet($this->month, $this->year, $this->signatories, $this->municipality, $this->batch),
            new MonthlyVisitItemsSheet($this->month, $this->year, $this->signatories),
        ];
    }
}
