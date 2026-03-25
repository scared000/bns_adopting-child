<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        $this->command->info('Importing offices...');
        $path = database_path('seeders/csv/offices.csv');

        if (!file_exists($path)) {
            $this->command->error("CSV file not found: {$path}");
            return;
        }

        $handle = fopen($path, 'r');

        // Strip UTF-8 BOM if present (common in Excel-exported CSVs)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read and clean headers
        $rawHeaders = fgetcsv($handle);
        $headers    = array_map(fn($h) => trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)), $rawHeaders);

        // Track which indexes have valid (non-empty) header names
        $validIndexes = array_keys(array_filter($headers, fn($h) => $h !== ''));
        $validHeaders = array_values(array_filter($headers, fn($h) => $h !== ''));

        $this->command->info('  Detected columns: ' . implode(', ', $validHeaders));

        $chunk     = [];
        $chunkSize = 500;
        $count     = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Only pick values at valid indexes
            $filteredRow = array_map(fn($i) => $row[$i] ?? null, $validIndexes);
            $data        = array_combine($validHeaders, $filteredRow);

            unset($data['id']); // let DB auto-increment

            $data['within_capitol']           = filter_var($data['within_capitol'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['can_be_multiple_services'] = filter_var($data['can_be_multiple_services'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['created_at']               = !empty($data['created_at']) ? $data['created_at'] : now();
            $data['updated_at']               = !empty($data['updated_at']) ? $data['updated_at'] : now();

            $chunk[] = $data;
            $count++;

            if (count($chunk) >= $chunkSize) {
                DB::table('offices')->insertOrIgnore($chunk);
                $chunk = [];
                $this->command->info("  → {$count} rows inserted...");
            }
        }

        if (!empty($chunk)) {
            DB::table('offices')->insertOrIgnore($chunk);
        }

        fclose($handle);
        $this->command->info("  ✅ Done! Total: {$count} rows.");
    }
}
