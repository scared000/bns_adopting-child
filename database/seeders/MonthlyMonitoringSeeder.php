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
        'WFA: Underweight | HFA: Stunted | WFH: Normal',
        'WFA: Severely Underweight | HFA: Normal | WFH: Wasted',
    ];

    private string $rehabilitatedStatus = 'WFA: Normal | HFA: Normal | WFH: Normal';

    private array $maintainedStatuses = [
        'WFA: Underweight | HFA: Stunted | WFH: Normal',
        'WFA: Normal | HFA: Stunted | WFH: Normal',
        'WFA: Underweight | HFA: Normal | WFH: Normal',
        'WFA: Severely Underweight | HFA: Stunted | WFH: Normal',
    ];

    private array $childrenData = [
        ['firstname' => 'Juan Miguel',   'lastname' => 'Santos',    'sex' => 'male',   'birthdate' => '2022-05-15'],
        ['firstname' => 'Maria Clara',   'lastname' => 'Reyes',     'sex' => 'female', 'birthdate' => '2022-08-20'],
        ['firstname' => 'Jose Antonio',  'lastname' => 'Garcia',    'sex' => 'male',   'birthdate' => '2021-11-10'],
        ['firstname' => 'Ana Rosa',      'lastname' => 'Cruz',      'sex' => 'female', 'birthdate' => '2022-02-28'],
        ['firstname' => 'Pedro Jose',    'lastname' => 'Mendoza',   'sex' => 'male',   'birthdate' => '2021-07-03'],
        ['firstname' => 'Luz Maria',     'lastname' => 'Torres',    'sex' => 'female', 'birthdate' => '2022-09-12'],
        ['firstname' => 'Carlo',         'lastname' => 'Ramos',     'sex' => 'male',   'birthdate' => '2021-04-25'],
        ['firstname' => 'Rosario',       'lastname' => 'Flores',    'sex' => 'female', 'birthdate' => '2022-01-07'],
        ['firstname' => 'Miguel',        'lastname' => 'Aquino',    'sex' => 'male',   'birthdate' => '2021-12-19'],
        ['firstname' => 'Cristina',      'lastname' => 'Bautista',  'sex' => 'female', 'birthdate' => '2022-06-30'],
        ['firstname' => 'Eduardo',       'lastname' => 'Dela Cruz', 'sex' => 'male',   'birthdate' => '2022-03-14'],
        ['firstname' => 'Marites',       'lastname' => 'Villanueva','sex' => 'female', 'birthdate' => '2021-10-05'],
    ];

    public function run(): void
    {
        $followUpMonth = now()->month;
        $followUpYear  = now()->year;

        $this->command->info('Seeding monthly monitoring data for: '
            . Carbon::createFromDate($followUpYear, $followUpMonth)->format('F Y'));

        $officeId         = $this->resolveOfficeId();
        $bnsId            = $this->resolveBnsId();
        $barangayCode     = DB::table('barangays')->value('brgyCode');
        $municipalityCode = DB::table('municipalities')->value('citymunCode');

        // Discover office_child_assigns columns dynamically
        $assignCols = array_column(
            DB::select("PRAGMA table_info(office_child_assigns)"),
            'name'
        );
        $this->command->line('  → office_child_assigns columns: ' . implode(', ', $assignCols));

        $created = 0;
        $skipped = 0;

        foreach ($this->childrenData as $index => $data) {

            // ── Create or find child ──────────────────────────────────────────
            $child = AdoptedChild::firstOrCreate(
                ['firstname' => $data['firstname'], 'lastname' => $data['lastname']],
                [
                    'firstname'          => $data['firstname'],
                    'lastname'           => $data['lastname'],
                    'sex'                => $data['sex'],
                    'birthdate'          => $data['birthdate'],
                    'purok'              => rand(1, 12),
                    'barangay_id'        => $barangayCode,
                    'municipality_id'    => $municipalityCode,
                    'nutritional_status' => 'Severely Underweight',
                    'lcr_registered'     => true,
                    'breastfed'          => (bool) rand(0, 1),
                ]
            );

            // ── Resolve assignment (find existing or create) ──────────────────
            $assignId = $this->resolveAssignId($child->id, $officeId, $bnsId, $assignCols);

            if (! $assignId) {
                $this->command->error("Could not resolve office_child_assigns for child id={$child->id}. Skipping.");
                $skipped += 2;
                continue;
            }

            // ── Baseline visit (~4 months ago) ────────────────────────────────
            $baselineDate = Carbon::createFromDate($followUpYear, $followUpMonth, rand(1, 28))
                ->subMonths(4);

            $hasBaseline = OfficeChildVisit::where('adopted_id', $child->id)
                ->where('visit_date', '<', Carbon::createFromDate($followUpYear, $followUpMonth, 1)->format('Y-m-d'))
                ->exists();

            if (! $hasBaseline) {
                OfficeChildVisit::create([
                    'office_assign_id' => $assignId,
                    'adopted_id'       => $child->id,
                    'office_id'        => $officeId,
                    'bns_id'           => $bnsId,
                    'visit_date'       => $baselineDate->format('Y-m-d'),
                    'weight'           => $this->randomWeight(6.0, 10.5),
                    'height'           => rand(65, 84),
                    'status'           => $this->baselineStatuses[$index % count($this->baselineStatuses)],
                    'visit_address'    => 'Purok ' . rand(1, 12),
                ]);
                $created++;
            } else {
                $skipped++;
            }

            // ── Follow-up visit (current month) ───────────────────────────────
            $hasFollowUp = OfficeChildVisit::where('adopted_id', $child->id)
                ->whereMonth('visit_date', $followUpMonth)
                ->whereYear('visit_date', $followUpYear)
                ->exists();

            if (! $hasFollowUp) {
                $isRehabbed = $index < 8;

                OfficeChildVisit::create([
                    'office_assign_id' => $assignId,
                    'adopted_id'       => $child->id,
                    'office_id'        => $officeId,
                    'bns_id'           => $bnsId,
                    'visit_date'       => Carbon::createFromDate($followUpYear, $followUpMonth, rand(1, 28))->format('Y-m-d'),
                    'weight'           => $isRehabbed
                        ? $this->randomWeight(10.5, 15.0)
                        : $this->randomWeight(8.0, 11.5),
                    'height'           => $isRehabbed ? rand(83, 98) : rand(72, 88),
                    'status'           => $isRehabbed
                        ? $this->rehabilitatedStatus
                        : $this->maintainedStatuses[$index % count($this->maintainedStatuses)],
                    'visit_address'    => 'Purok ' . rand(1, 12),
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✔ Done — {$created} visit records created, {$skipped} skipped.");
        $this->command->line('');
        $this->command->info('Export from the Dashboard using:');
        $this->command->line('  Month : ' . Carbon::createFromDate($followUpYear, $followUpMonth)->format('F'));
        $this->command->line("  Year  : {$followUpYear}");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Find or create an office_child_assigns row for this child.
     * Builds the insert dynamically so it works regardless of your exact schema.
     */
    private function resolveAssignId(int $childId, ?int $officeId, ?int $bnsId, array $cols): ?int
    {
        $existing = DB::table('office_child_assigns')
            ->where('adopted_id', $childId)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $row = ['created_at' => now(), 'updated_at' => now()];

        if (in_array('adopted_id', $cols))                    $row['adopted_id']    = $childId;
        if (in_array('office_id', $cols)    && $officeId)     $row['office_id']     = $officeId;
        if (in_array('bns_id', $cols)       && $bnsId)        $row['bns_id']        = $bnsId;
        if (in_array('start_date', $cols))                    $row['start_date']    = now()->subMonths(5)->format('Y-m-d');
        if (in_array('end_date', $cols))                      $row['end_date']      = now()->addMonths(1)->format('Y-m-d');
        if (in_array('status', $cols))                        $row['status']        = 'active';
        if (in_array('assigned_at', $cols))                   $row['assigned_at']   = now()->subMonths(5)->format('Y-m-d');
        if (in_array('date_assigned', $cols))                 $row['date_assigned'] = now()->subMonths(5)->format('Y-m-d');
        if (in_array('remarks', $cols))                       $row['remarks']       = 'Seeded for testing';

        try {
            return DB::table('office_child_assigns')->insertGetId($row);
        } catch (\Throwable $e) {
            $this->command->error('Failed to insert office_child_assigns: ' . $e->getMessage());
            $this->command->line('  Row attempted: ' . json_encode($row));
            return null;
        }
    }

    private function randomWeight(float $min, float $max): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 1);
    }

    private function resolveOfficeId(): ?int
    {
        $row = DB::table('offices')->first();
        if ($row) {
            $this->command->line('  → Using office id=' . $row->id);
            return $row->id;
        }
        $this->command->error('No rows in [offices] table.');
        return null;
    }

    private function resolveBnsId(): ?int
    {
        $row = DB::table('barangay_nutrition_scholars')->first();
        if ($row) {
            $this->command->line('  → Using BNS id=' . $row->id);
            return $row->id;
        }
        $this->command->error('No rows in [barangay_nutrition_scholars] table.');
        return null;
    }
}
