<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsgcSeeder extends Seeder
{
    public function run(): void
    {
        // Disable query log to save memory
        DB::disableQueryLog();

        $this->importProvinces();
        $this->importMunicipalities();
        $this->importBarangays();
    }

    private function importProvinces(): void
    {
        $this->command->info('Importing provinces...');
        $path = database_path('seeders/csv/provinces.csv');
        $this->importCsv($path, 'provinces', ['psgcCode', 'provDesc', 'regCode', 'provCode']);
    }

    private function importMunicipalities(): void
    {
        $this->command->info('Importing municipalities...');
        $path = database_path('seeders/csv/municipalities.csv');
        $this->importCsv($path, 'municipalities', ['psgcCode', 'citymunDesc', 'regDesc', 'provCode', 'citymunCode']);
    }

    private function importBarangays(): void
    {
        $this->command->info('Importing barangays...');
        $path = database_path('seeders/csv/barangays.csv');
        $this->importCsv($path, 'barangays', ['brgyCode', 'brgyDesc', 'regCode', 'provCode', 'citymunCode']);
    }

    private function importCsv(string $path, string $table, array $columns): void
    {
        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header row

        $chunk = [];
        $chunkSize = 500; // insert 500 rows at a time
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($columns, array_slice($row, 1));
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $chunk[] = $data;
            $count++;

            if (count($chunk) >= $chunkSize) {
                DB::table($table)->insertOrIgnore($chunk);
                $chunk = [];
                $this->command->info("  → {$count} rows inserted...");
            }
        }

        // Insert remaining rows
        if (!empty($chunk)) {
            DB::table($table)->insertOrIgnore($chunk);
        }

        fclose($handle);
        $this->command->info("  ✅ Done! Total: {$count} rows.");
    }
}
