<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Carlos Morales', 'carlos.morales@bolsa.test'],
            ['Luis Carrasco', 'luis.carrasco@bolsa.test'],
            ['Angerly Rojas', 'angerly.rojas@bolsa.test'],
        ];

        foreach ($users as [$name, $email]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password123')],
            );
        }
    }
}
