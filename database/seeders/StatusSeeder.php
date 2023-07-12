<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::factory()->create([
            'name' => 'admin',
        ]);
        Status::factory()->create([
            'name' => 'éditeur',
        ]);
        Status::factory()->create([
            'name' => 'rédacteur',
        ]);
    }
}
