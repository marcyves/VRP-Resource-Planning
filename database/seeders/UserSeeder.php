<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Marc',
            'email' => 'm@xdm.fr',
            'email_verified_at' => now(),
            'password' => '$2y$10$CgeZsj4zB1rlJcaL4a9IIuqvIR2aZ/t9pd4cMzi.welkWS1UOUBX2', // topsecret
        ]);
        
        User::factory(10)->create();
    }
}
