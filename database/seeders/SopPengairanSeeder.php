<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SopPengairan;

class SopPengairanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SopPengairan::insert([
            [
                "id_penanaman" => 1,
                'nama' => 'temperature',
                'min' => '25',
                'max' => '33',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "id_penanaman" => 1,
                'nama' => 'humidity',
                'min' => '60',
                'max' => '69',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "id_penanaman" => 1,
                'nama' => 'soil_moisture',
                'min' => '50',
                'max' => '69',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
