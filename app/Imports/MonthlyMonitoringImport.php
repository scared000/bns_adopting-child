<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyMonitoringImport implements WithMultipleSheets
{
    private MonthlyNutritionalStatusImport $statusSheet;

    public function __construct()
    {
        $this->statusSheet = new MonthlyNutritionalStatusImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->statusSheet,
        ];
    }

    public function getImported(): int { return $this->statusSheet->getImported(); }
    public function getCreated(): int  { return $this->statusSheet->getCreated(); }
    public function getSkipped(): int  { return $this->statusSheet->getSkipped(); }
    public function getErrors(): array { return $this->statusSheet->getErrors(); }
}
