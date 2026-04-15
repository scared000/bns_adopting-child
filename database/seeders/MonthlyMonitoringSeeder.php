<?php

namespace Database\Seeders;

use App\Models\AdoptedChild;
use App\Models\OfficeChildVisit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthlyMonitoringSeeder extends Seeder
{
    private array $baselineStatuses = [
        'WFA: Severely Underweight | HFA: Severely Stunted | WFH: Normal',
        'WFA: Underweight | HFA: Stunted | WFH: Wasted',
        'WFA: Severely Underweight | HFA: Normal | WFH: Severely Wasted',
        'WFA: Underweight | HFA: Stunted | WFH: Normal',
        'WFA: Severely Underweight | HFA: Severely Stunted | WFH: Wasted',
        'WFA: Underweight | HFA: Normal | WFH: Wasted',
        'WFA: Normal | HFA: Severely Stunted | WFH: Normal',
        'WFA: Severely Underweight | HFA: Stunted | WFH: Wasted',
        'WFA: Underweight | HFA: Stunted | WFH: Wasted',
        'WFA: Severely Underweight | HFA: Severely Stunted | WFH: Severely Wasted',
    ];

    private string $rehabilitatedStatus = 'WFA: Normal | HFA: Normal | WFH: Normal';

    private array $maintainedStatuses = [
        'WFA: Underweight | HFA: Stunted | WFH: Normal',
        'WFA: Normal | HFA: Stunted | WFH: Normal',
        'WFA: Underweight | HFA: Normal | WFH: Normal',
        'WFA: Severely Underweight | HFA: Stunted | WFH: Normal',
    ];

    private array $maleNames = [
        'Juan Miguel', 'Jose Antonio', 'Pedro Jose', 'Carlo', 'Miguel', 'Eduardo',
        'Marco', 'Rafael', 'Santiago', 'Andres', 'Gabriel', 'Luis', 'Francis', 'Danilo', 'Fernando',
        'Rico', 'Bryan', 'Kenneth', 'Jericho', 'Mark'
    ];

    private array $femaleNames = [
        'Maria Clara', 'Ana Rosa', 'Luz Maria', 'Rosario', 'Cristina', 'Marites',
        'Isabella', 'Camille', 'Angelica', 'Patricia', 'Marianne', 'Grace', 'Elena', 'Carmen', 'Rosa',
        'Mikaela', 'Joy', 'Angela', 'Sarah', 'Jane'
    ];

    private array $lastNames = [
        'Santos', 'Reyes', 'Garcia', 'Cruz', 'Mendoza', 'Torres', 'Ramos', 'Flores',
        'Aquino', 'Bautista', 'Dela Cruz', 'Villanueva', 'Lopez', 'Morales', 'Rivera',
        'Gonzales', 'Chavez', 'Vergara', 'Medina', 'Castro', 'Pascual', 'Dimaculangan'
    ];

    public function run(): void
    {
        $this->command->warn('Generating realistic 4-year NTP pipeline data (2 visits/mo, 3-4 mos cycle)...');

        $officeId         = $this->resolveOfficeId();
        $bnsId            = $this->resolveBnsId();

        if (!$officeId || !$bnsId) {
            $this->command->error('Seeder aborted: Missing Office or BNS.');
            return;
        }

        $barangayCode     = DB::table('barangays')->value('brgyCode');
        $municipalityCode = DB::table('municipalities')->value('citymunCode');

        $assignCols = array_column(
            DB::select("PRAGMA table_info(office_child_assigns)"),
            'name'
        );

        // Generate exactly 120 unique name combinations to prevent DB collisions
        $namePool = $this->generateUniqueNames(120);

        $totalChildren = 120;
        $created = 0;
        $skipped = 0;

        $this->command->getOutput()->progressStart($totalChildren);

        DB::transaction(function () use (
            $namePool, $totalChildren, $officeId, $bnsId,
            $barangayCode, $municipalityCode, $assignCols, &$created, &$skipped
        ) {
            $now = Carbon::now();

            for ($i = 0; $i < $totalChildren; $i++) {
                $nameData = $namePool[$i];

                // Random duration: 3 to 4 months (Standard NTP maximum monitoring period)
                $durationMonths = rand(3, 4);

                // Stagger start dates across the last 4 years, ensuring the cycle finishes before NOW
                $maxStart = $now->subMonths($durationMonths)->startOfMonth();
                $minStart = $now->copy()->subYears(4)->startOfMonth();

                $startDate = Carbon::createFromTimestamp(
                    rand($minStart->timestamp, $maxStart->timestamp)
                )->startOfMonth();

                // Birthdate: Typically 6 months to 5 years old upon entry
                $birthdate = $startDate->copy()->subMonths(rand(12, 59))->format('Y-m-d');

                $child = AdoptedChild::firstOrCreate(
                    ['firstname' => $nameData['firstname'], 'lastname' => $nameData['lastname']],
                    [
                        'firstname'          => $nameData['firstname'],
                        'lastname'           => $nameData['lastname'],
                        'sex'                => $nameData['sex'],
                        'birthdate'          => $birthdate,
                        'purok'              => rand(1, 12),
                        'barangay_id'        => $barangayCode,
                        'municipality_id'    => $municipalityCode,
                        'nutritional_status' => 'Severely Underweight',
                        'lcr_registered'     => (bool) rand(0, 1),
                        'breastfed'          => (bool) rand(0, 1),
                    ]
                );

                $assignId = $this->resolveAssignId(
                    $child->id, $officeId, $bnsId, $assignCols,
                    $startDate->format('Y-m-d'),
                    $startDate->copy()->addMonths($durationMonths)->subDay()->format('Y-m-d')
                );

                if (!$assignId) {
                    $skipped += ($durationMonths * 2);
                    $this->command->getOutput()->progressAdvance();
                    continue;
                }

                // Determine final outcome (50% chance of actual rehabilitation in PH NTP reality)
                $willRehab = rand(1, 10) <= 5;

                // Base metrics at entry (1-2 years old typically)
                $baseWeight = 7.5 + (rand(-50, 100) / 100);
                $baseHeight = 72.0 + (rand(-20, 50) / 100);

                // Simulate TWICE A MONTH visits for the duration
                for ($m = 0; $m < $durationMonths; $m++) {
                    $currentMonth = $startDate->copy()->addMonths($m);

                    // Visit 1: 1st or 2nd week (Days 5-12)
                    $visit1Date = $currentMonth->copy()->addDays(rand(4, 12));
                    // Visit 2: 3rd or 4th week (Days 16-24)
                    $visit2Date = $currentMonth->copy()->addDays(rand(15, 24));

                    // Realistic weight gain: ~0.15kg per 2 weeks with feeding program
                    $weightGainPerVisit = 0.12 + (rand(0, 50) / 1000);
                    $heightGainPerMonth = 0.8 + (rand(0, 20) / 100);

                    // Calculate metrics for this specific month
                    $monthBaseWeight = $baseWeight + ($m * 0.25) + ($weightGainPerVisit * ($m * 2));
                    $monthBaseHeight = $baseHeight + ($m * $heightGainPerMonth);

                    // Determine Status for this month
                    if ($m === 0) {
                        $status = $this->baselineStatuses[array_rand($this->baselineStatuses)];
                    } elseif ($m === $durationMonths - 1) {
                        // Final month (Graduation month)
                        $status = $willRehab ? $this->rehabilitatedStatus : $this->maintainedStatuses[array_rand($this->maintainedStatuses)];
                        if ($willRehab) $monthBaseWeight += 0.5; // Boost to hit "Normal" bracket
                    } else {
                        // Middle months
                        $status = $this->maintainedStatuses[array_rand($this->maintainedStatuses)];
                    }

                    // Insert Visit 1
                    $created += $this->insertVisit(
                        $assignId, $child->id, $officeId, $bnsId,
                        $visit1Date, round($monthBaseWeight, 1), round($monthBaseHeight), $status
                    );

                    // Insert Visit 2 (Slightly heavier, same status)
                    $created += $this->insertVisit(
                        $assignId, $child->id, $officeId, $bnsId,
                        $visit2Date, round($monthBaseWeight + $weightGainPerVisit, 1), round($monthBaseHeight, 0), $status
                    );
                }

                $this->command->getOutput()->progressAdvance();
            }
        });

        $this->command->getOutput()->progressFinish();
        $this->command->info("✔ Done — {$created} visit records created across staggered 3-4 month cycles.");
        $this->command->line('');
        $this->command->info('Realistic NTP behavior simulated:');
        $this->command->line('  • 2 visits per month');
        $this->command->line('  • Auto-graduation after 3 or 4 months');
        $this->command->line('  • Continuous pipeline of new entries over the last 4 years.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function insertVisit($assignId, $childId, $officeId, $bnsId, Carbon $date, $weight, $height, $status): int
    {
        $exists = OfficeChildVisit::where('adopted_id', $childId)
            ->whereDate('visit_date', $date->format('Y-m-d'))
            ->exists();

        if (!$exists) {
            OfficeChildVisit::create([
                'office_assign_id' => $assignId,
                'adopted_id'       => $childId,
                'office_id'        => $officeId,
                'bns_id'           => $bnsId,
                'visit_date'       => $date->format('Y-m-d'),
                'weight'           => $weight,
                'height'           => $height,
                'status'           => $status,
                'visit_address'    => 'Purok ' . rand(1, 12),
            ]);
            return 1;
        }
        return 0;
    }

    private function generateUniqueNames(int $count): array
    {
        $pool = [];
        $used = [];

        while (count($pool) < $count) {
            $sex = array_rand([0, 1]) === 0 ? 'male' : 'female';
            $firstname = $sex === 'male'
                ? $this->maleNames[array_rand($this->maleNames)]
                : $this->femaleNames[array_rand($this->femaleNames)];
            $lastname = $this->lastNames[array_rand($this->lastNames)];

            $key = strtolower($firstname . $lastname);
            if (!in_array($key, $used)) {
                $used[] = $key;
                $pool[] = [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'sex'       => $sex
                ];
            }
        }
        return $pool;
    }

    private function resolveAssignId(int $childId, ?int $officeId, ?int $bnsId, array $cols, string $startDate, string $endDate): ?int
    {
        $existing = DB::table('office_child_assigns')->where('adopted_id', $childId)->first();
        if ($existing) return $existing->id;

        $row = ['created_at' => now(), 'updated_at' => now()];
        if (in_array('adopted_id', $cols))                    $row['adopted_id']    = $childId;
        if (in_array('office_id', $cols)    && $officeId)     $row['office_id']     = $officeId;
        if (in_array('bns_id', $cols)       && $bnsId)        $row['bns_id']        = $bnsId;
        if (in_array('start_date', $cols))                    $row['start_date']    = $startDate;
        if (in_array('end_date', $cols))                      $row['end_date']      = $endDate;
        if (in_array('status', $cols))                        $row['status']        = 'graduated'; // Past cycles are graduated
        if (in_array('assigned_at', $cols))                   $row['assigned_at']   = $startDate;
        if (in_array('date_assigned', $cols))                 $row['date_assigned'] = $startDate;
        if (in_array('remarks', $cols))                       $row['remarks']       = 'Seeded: Completed NTP cycle';

        try {
            return DB::table('office_child_assigns')->insertGetId($row);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveOfficeId(): ?int
    {
        return DB::table('offices')->first()?->id;
    }

    private function resolveBnsId(): ?int
    {
        return DB::table('barangay_nutrition_scholars')
            ->whereNotNull('user_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'barangay_nutrition_scholars.user_id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->where('roles.name', 'bns');
            })->value('id');
    }
}
