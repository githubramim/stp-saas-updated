<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $institute = Institute::firstOrCreate(
            ['code' => 'DEMO01'],
            [
                'name' => 'Demo Institute',
                'mobile' => '01700000000',
                'email' => 'institute@demo.com',
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@stp.com'],
            [
                'name' => 'Institute Admin',
                'password' => Hash::make('admin123'),
                'phone' => '01700000000',
                'role' => 'admin',
                'institute_id' => $institute->id,
            ]
        );
    }
}
