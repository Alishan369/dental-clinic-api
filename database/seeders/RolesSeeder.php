<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'doctor', 'receptionist', 'accountant'];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role],
                ['id' => Str::uuid()]
            );
        }
    }
}
