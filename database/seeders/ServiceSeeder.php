<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create([
            'name' => 'Rental PS 4',
            'description' => 'Rental PlayStation 4 per sesi',
            'price' => 30000
        ]);

        Service::create([
            'name' => 'Rental PS 5',
            'description' => 'Rental PlayStation 5 per sesi',
            'price' => 40000
        ]);
    }
}
