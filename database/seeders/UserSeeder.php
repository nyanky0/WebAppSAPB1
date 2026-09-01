<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = \App\Models\Role::where('name', 'Super Admin')->first();
        $systemAdminRole = \App\Models\Role::where('name', 'System Admin')->first();
        
        // 1. Manager (Super Admin - with offline save / bypass test capability)
        \App\Models\User::create([
            'name' => 'Manager',
            'username' => 'manager',
            'password' => \Illuminate\Support\Facades\Hash::make('P@ssw0rd'),
            'sap_user' => 'manager',
            'sap_password' => 'P@ssw0rd',
            'role_id' => $superAdminRole?->id,
        ]);

        // 2. User 2 (System Admin - Demoted role level below Super Admin)
        \App\Models\User::create([
            'name' => 'User 2',
            'username' => 'userweb2',
            'password' => \Illuminate\Support\Facades\Hash::make('P@ssw0rd'),
            'sap_user' => 'userweb2',
            'sap_password' => 'P@ssw0rd',
            'role_id' => $systemAdminRole?->id,
        ]);
    }
}
