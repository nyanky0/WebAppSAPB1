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
        $role = \App\Models\Role::where('name', 'Super Admin')->first();
        
        \App\Models\User::create([
            'name' => 'Manager',
            'username' => 'manager',
            'password' => \Illuminate\Support\Facades\Hash::make('P@ssw0rd'),
            'role_id' => $role?->id,
        ]);
    }
}
