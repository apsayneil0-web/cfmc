<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'johncruz',
                'email' => 'johncruz@coop.com',
                'password' => 'DemoPass123!',
                'Status' => 'Active',
                'RoleID' => 1,
                'PhoneNumber' => '09123456789',
                'FirstTimeLogin' => 0,
            ],
            [
                'username' => 'mariasantos',
                'email' => 'mariasantos@coop.com',
                'password' => 'DemoPass123!',
                'Status' => 'Active',
                'RoleID' => 2,
                'PhoneNumber' => '09234567890',
                'FirstTimeLogin' => 0,
            ],
            [
                'username' => 'carlosreyes',
                'email' => 'carlosreyes@coop.com',
                'password' => 'DemoPass123!',
                'Status' => 'Active',
                'RoleID' => 3,
                'PhoneNumber' => '09345678901',
                'FirstTimeLogin' => 0,
            ],
            [
                'username' => 'annalopez',
                'email' => 'annalopez@coop.com',
                'password' => 'DemoPass123!',
                'Status' => 'Active',
                'RoleID' => 1,
                'PhoneNumber' => '09456789012',
                'FirstTimeLogin' => 0,
            ],
            [
                'username' => 'markdelacruz',
                'email' => 'markdelacruz@coop.com',
                'password' => 'DemoPass123!',
                'Status' => 'Active',
                'RoleID' => 3,
                'PhoneNumber' => '09567890123',
                'FirstTimeLogin' => 0,
            ],
            [
                'username' => 'admin_demo',
                'email' => 'admin@coop.com',
                'password' => 'AdminDemo123!',
                'Status' => 'Active',
                'RoleID' => 1,
                'PhoneNumber' => '09000000001',
                'FirstTimeLogin' => 0,
            ],
            [
                'username' => 'farmer_demo',
                'email' => 'farmer@coop.com',
                'password' => 'FarmerDemo123!',
                'Status' => 'Active',
                'RoleID' => 3,
                'PhoneNumber' => '09000000002',
                'FirstTimeLogin' => 0,
            ],
        ];

        foreach ($users as $user) {
            $plainPassword = $user['password'];
            $user['password'] = Hash::make($plainPassword);

            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $user
            );
        }
    }
}