<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            OfficeSeeder::class,
            PsgcSeeder::class,
            WhoGrowthStandardsSeeder::class,
        ]);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'bns', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'office', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::create([
            'firstname'  => 'Super',
            'lastname'   => 'Admin',
            'email'      => 'superadmin@admin.com',
            'password'   => Hash::make('admin12345'),
        ]);

        $user->assignRole('super_admin');
    }
}
