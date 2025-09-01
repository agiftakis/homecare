<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'andygis4116',
            'email' => 'andrewgis43@gmail.com', 
            'password' => Hash::make('Malaka6969411*'), 
            'agency_id' => null, // Super admin does not belong to an agency
            'role' => 'super_admin',
        ]);
    }
}

