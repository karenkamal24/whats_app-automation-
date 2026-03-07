<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
   use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FilamentAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


public function run(): void
{
    User::create([
    'name' => 'Filament Admin',
    'email' => 'admin@gmail.com',
    'password' => '12345678',
    'is_admin' => true,
]);
}
}
