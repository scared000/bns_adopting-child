<?php

namespace App\Exports;

use App\Models\OfficeChildVisit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyMonitoringExport implements WithMultipleSheets
{
    public function __construct(
        public int $month,
        public int $year
    ) {}

    public function sheets(): array
    {
        return [
            new MonthlyNutritionalStatusSheet($this->month, $this->year),
            new MonthlyVisitItemsSheet($this->month, $this->year),
        ];
    }
}
