<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/stations.json');
        $stations = json_decode(file_get_contents($path), true);

        foreach ($stations as $s) {
            Station::query()->updateOrCreate(
                ['code' => $s['code']],
                [
                    'name' => $s['name'],
                    'side' => $s['side'],
                    'is_own_station' => $s['is_own_station'],
                    'keterangan' => $s['keterangan'] ?: null,
                ]
            );
        }
    }
}
