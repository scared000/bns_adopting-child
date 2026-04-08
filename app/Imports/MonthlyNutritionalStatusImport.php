<?php

namespace App\Imports;

use App\Models\AdoptedChild;
use App\Models\OfficeChildVisit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MonthlyNutritionalStatusImport implements ToCollection, WithStartRow
{
    private int $imported    = 0;
    private int $created     = 0;
    private int $skipped     = 0;
    private array $errors    = [];

    public function startRow(): int { return 12; }

    public function collection(Collection $rows)
    {
        // Each follow-up block: [date, age, wt, wfa, ht, hfa, wfh]
        // Starts at col 6, repeats every 7 columns, 4 times
        $visitOffsets = [6, 13, 20, 27];

        foreach ($rows as $index => $row) {
            $rawName = trim($row[0] ?? '');

            // Skip empty rows
            if (!$rawName) continue;

            $namePart = preg_replace('/^\d+\.\s*/', '', $rawName);

            // Skip footer/summary rows — no sex value means it's not a child row
            if (
                is_null($row[1]) ||
                str_starts_with(strtolower($namePart), 'prepared') ||
                str_starts_with(strtolower($namePart), 'nd-')
            ) {
                continue;
            }

            try {
                // Handle both "Lastname, Firstname" and "Firstname Lastname" (no comma)
                if (str_contains($namePart, ',')) {
                    [$lastname, $firstname] = array_map('trim', explode(',', $namePart, 2));
                } else {
                    $parts     = explode(' ', $namePart, 2);
                    $firstname = $parts[0];
                    $lastname  = $parts[1] ?? '';
                }

                $sex = trim($row[1] ?? '');
                $sex = match(strtoupper($sex)) {
                    'M'         => 'Male',
                    'F', 'F-PS' => 'Female',
                    default     => $sex,
                };

                // ── Collect all valid visits ──────────────────────────────
                $visits = [];
                foreach ($visitOffsets as $offset) {
                    $date   = $this->parseDate($row[$offset]       ?? '');
                    $weight = $this->parseNumeric($row[$offset + 2] ?? '');
                    $height = $this->parseNumeric($row[$offset + 4] ?? '');
                    $wfa    = trim($row[$offset + 3] ?? '');
                    $hfa    = trim($row[$offset + 5] ?? '');
                    $wfh    = trim($row[$offset + 6] ?? '');
                    $age    = (int) ($row[$offset + 1] ?? 0);

                    if ($date && ($weight || $height)) {
                        $visits[] = compact('date', 'weight', 'height', 'wfa', 'hfa', 'wfh', 'age');
                    }
                }

                // ── Sort by actual date so we always get the truly latest ──
                usort($visits, fn($a, $b) => strcmp($a['date'], $b['date']));

                // ── Use latest visit for child record ─────────────────────
                $latestVisit   = !empty($visits) ? end($visits)   : null;
                $firstVisit    = !empty($visits) ? $visits[0]     : null;
                $referenceDate = $latestVisit['date'] ?? $firstVisit['date'] ?? null;
                $ageMonths     = $latestVisit ? $latestVisit['age'] : (int) ($row[2] ?? 0);

                $latestStatus = $latestVisit
                    ? $this->buildStatus($latestVisit['wfa'], $latestVisit['hfa'], $latestVisit['wfh'])
                    : '';

                // ── Find or create child ──────────────────────────────────
                $child = AdoptedChild::whereRaw('LOWER(lastname) = ?',  [strtolower($lastname)])
                    ->whereRaw('LOWER(firstname) = ?', [strtolower($firstname)])
                    ->first();

                if (!$child) {
                    $child = AdoptedChild::create([
                        'firstname'          => $firstname,
                        'lastname'           => $lastname,
                        'sex'                => $sex,
                        'birthdate'          => $this->estimateBirthdate($ageMonths, $referenceDate),
                        'weight_kg'          => $latestVisit['weight'] ?? null,
                        'height_cm'          => $latestVisit['height'] ?? null,
                        'nutritional_status' => $latestStatus,
                    ]);
                    $this->created++;
                } else {
                    $child->update([
                        'birthdate'          => $this->estimateBirthdate($ageMonths, $referenceDate),
                        'weight_kg'          => $latestVisit['weight'] ?? $child->weight_kg,
                        'height_cm'          => $latestVisit['height'] ?? $child->height_cm,
                        'nutritional_status' => $latestStatus ?: $child->nutritional_status,
                    ]);
                }

                // ── Save baseline visit ───────────────────────────────────
                $baselineStatus = $this->expandAbbreviatedStatus(trim($row[5] ?? ''));
                $blWeight       = $this->parseNumeric($row[3] ?? '');
                $blHeight       = $this->parseNumeric($row[4] ?? '');

                if ($firstVisit && ($blWeight || $blHeight)) {
                    $blAgeMonths           = (int) ($row[2] ?? 0); // use actual baseline age from col[2]
                    $fuAgeMonths           = $firstVisit['age'];
                    $monthsDiff            = max(1, $fuAgeMonths - $blAgeMonths);
                    $estimatedBaselineDate = Carbon::parse($firstVisit['date'])
                        ->subMonths($monthsDiff)
                        ->format('Y-m-d');

                    OfficeChildVisit::updateOrCreate(
                        ['adopted_id' => $child->id, 'visit_date' => $estimatedBaselineDate],
                        ['weight' => $blWeight, 'height' => $blHeight, 'status' => $baselineStatus]
                    );
                }

                // ── Save ALL follow-up visits ─────────────────────────────
                foreach ($visits as $visit) {
                    OfficeChildVisit::updateOrCreate(
                        ['adopted_id' => $child->id, 'visit_date' => $visit['date']],
                        [
                            'weight' => $visit['weight'],
                            'height' => $visit['height'],
                            'status' => $this->buildStatus($visit['wfa'], $visit['hfa'], $visit['wfh']),
                        ]
                    );
                }

                $this->imported++;

            } catch (\Exception $e) {
                $this->skipped++;
                $this->errors[] = "Row " . ($index + 12) . " ({$rawName}): " . $e->getMessage();
            }
        }
    }

    private function estimateBirthdate(int $ageMonths, ?string $referenceDate): ?string
    {
        if (!$referenceDate || $ageMonths <= 0) {
            \Log::info("Birthdate failed for: Age $ageMonths, Date $referenceDate");
            return null;
        }

        return Carbon::parse($referenceDate)
            ->subMonths($ageMonths)
            ->format('Y-m-d');
    }

    private function parseDate(mixed $value): ?string
    {
        if (!$value || $value === '—') return null;

        $parsed = null;

        if (is_numeric($value)) {
            // Convert Excel numeric date to Carbon instance
            $parsed = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
        } else {
            $value = trim((string) $value);
            $value = str_replace(['.', ' '], '/', $value);

            $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d'];
            foreach ($formats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $value)->startOfDay();
                    break; // Stop loop if we successfully parsed it
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // If we couldn't parse it or it's outside valid bounds
        if (!$parsed || $parsed->year < 1900 || $parsed->isFuture()) {
            return null;
        }

        return $parsed->format('Y-m-d');
    }

    private function expandAbbreviatedStatus(string $abbreviated): string
    {
        $reverseMap = [
            'SUW' => 'Severely Underweight',
            'UW'  => 'Underweight',
            'N'   => 'Normal',
            'OW'  => 'Overweight',
            'OB'  => 'Obese',
            'SST' => 'Severely Stunted',
            'ST'  => 'Stunted',
            'SW'  => 'Severely Wasted',
            'W'   => 'Wasted',
        ];

        $parts  = array_map('trim', explode('/', $abbreviated));
        $keys   = ['WFA', 'HFA', 'WFH'];
        $result = [];

        foreach ($parts as $i => $abbr) {
            if (isset($keys[$i]) && isset($reverseMap[$abbr])) {
                $result[] = $keys[$i] . ': ' . $reverseMap[$abbr];
            }
        }

        return implode(' | ', $result);
    }

    private function buildStatus(string $wfa, string $hfa, string $wfh): string
    {
        $reverseMap = [
            'SUW' => 'Severely Underweight',
            'UW'  => 'Underweight',
            'N'   => 'Normal',
            'OW'  => 'Overweight',
            'OB'  => 'Obese',
            'SST' => 'Severely Stunted',
            'ST'  => 'Stunted',
            'SW'  => 'Severely Wasted',
            'W'   => 'Wasted',
        ];

        $parts = [];
        if ($wfa && $wfa !== '—') $parts[] = 'WFA: ' . ($reverseMap[$wfa] ?? $wfa);
        if ($hfa && $hfa !== '—') $parts[] = 'HFA: ' . ($reverseMap[$hfa] ?? $hfa);
        if ($wfh && $wfh !== '—') $parts[] = 'WFH: ' . ($reverseMap[$wfh] ?? $wfh);

        return implode(' | ', $parts);
    }

    private function parseNumeric(mixed $value): ?float
    {
        $v = trim((string) $value);
        return ($v && $v !== '—') ? (float) $v : null;
    }

    public function getImported(): int { return $this->imported; }
    public function getCreated(): int  { return $this->created; }
    public function getSkipped(): int  { return $this->skipped; }
    public function getErrors(): array { return $this->errors; }
}
