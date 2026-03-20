<?php

namespace Database\Seeders;

use App\Models\WhoGrowthStandard;
use Illuminate\Database\Seeder;

class WhoGrowthStandardsSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            // 0-5 years (WHO Child Growth Standards 2006)
            ['indicator' => 'wfa', 'sex' => 'male',   'path' => database_path('seeders/who-data/wfa-boys.csv')],
            ['indicator' => 'wfa', 'sex' => 'female', 'path' => database_path('seeders/who-data/wfa-girls.csv')],
            ['indicator' => 'hfa', 'sex' => 'male',   'path' => database_path('seeders/who-data/hfa-boys.csv')],
            ['indicator' => 'hfa', 'sex' => 'female', 'path' => database_path('seeders/who-data/hfa-girls.csv')],
            ['indicator' => 'wfh', 'sex' => 'male',   'path' => database_path('seeders/who-data/wfh-boys.csv')],
            ['indicator' => 'wfh', 'sex' => 'female', 'path' => database_path('seeders/who-data/wfh-girls.csv')],

            // 5-19 years (WHO Growth Reference 2007 — BMI-for-age)
            ['indicator' => 'bfa', 'sex' => 'male',   'path' => database_path('seeders/who-data/bfa-boys.csv')],
            ['indicator' => 'bfa', 'sex' => 'female', 'path' => database_path('seeders/who-data/bfa-girls.csv')],
        ];

        foreach ($files as $file) {
            if (!file_exists($file['path'])) {
                $this->command->error("File not found: {$file['path']}");
                continue;
            }

            $csv     = array_map('str_getcsv', file($file['path']));
            $headers = array_shift($csv);

            $rows = [];
            foreach ($csv as $row) {
                if (count($row) < 4) continue;

                $rows[] = [
                    'indicator'  => $file['indicator'],
                    'sex'        => $file['sex'],
                    'key_value'  => (float) $row[0],
                    'l'          => (float) $row[1],
                    'm'          => (float) $row[2],
                    's'          => (float) $row[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            WhoGrowthStandard::where('indicator', $file['indicator'])
                ->where('sex', $file['sex'])
                ->delete();

            foreach (array_chunk($rows, 100) as $chunk) {
                WhoGrowthStandard::insert($chunk);
            }

            $this->command->info("Seeded {$file['indicator']} ({$file['sex']}): " . count($rows) . " rows");
        }

        $this->command->info('WHO Growth Standards seeded successfully!');
    }
}
