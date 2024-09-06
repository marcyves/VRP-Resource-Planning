<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::factory()->create([
            'name' => 'Demo Company',
            'bill_prefix' => "DEMO"
        ]);
        Company::factory()->create([
            'name' => 'XDM Consulting',
            'bill_prefix' => "XDM"
        ]);
    }
}
