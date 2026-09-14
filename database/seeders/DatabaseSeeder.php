<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (range(1, 6) as $number) {
            Machine::firstOrCreate(['name' => ($number <= 4 ? 'Lavadora ' : 'Secadora ').sprintf('%02d', $number)], [
                'type' => $number <= 4 ? 'lavadora' : 'secadora',
                'capacity' => $number <= 4 ? 8 : 10,
                'location' => 'Lavandería · Planta baja',
                'status' => $number === 4 ? 'mantenimiento' : 'disponible',
            ]);
        }
    }
}
