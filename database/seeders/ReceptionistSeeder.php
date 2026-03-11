<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ReceptionistSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'receptionist')->first();

        if (!$role) {
            throw new \Exception('Receptionist role not found. Run RolesSeeder first.');
        }

        User::firstOrCreate(
            ['email' => 'receptionist@clinic.com'],
            [
                'name'     => 'Clinic Receptionist',
                'password' => Hash::make('password123'),
                'phone'    => '9999999999',
                'address'  => 'Clinic Address',
                'role_id'  => $role->id,
            ]
        );
    }
}