<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = [
            ['code' => 'I', 'name' => 'Jalur I', 'jenis' => 'Sepur lurus (Commuter Line)', 'sort_order' => 1],
            ['code' => 'II', 'name' => 'Jalur II', 'jenis' => 'Sepur badug', 'sort_order' => 2],
            ['code' => 'III', 'name' => 'Jalur III', 'jenis' => 'Sepur badug', 'sort_order' => 3],
            ['code' => 'IV', 'name' => 'Jalur IV', 'jenis' => 'Sepur badug', 'sort_order' => 4],
            ['code' => 'V', 'name' => 'Jalur V', 'jenis' => 'Sepur badug', 'sort_order' => 5],
            ['code' => 'VI', 'name' => 'Jalur VI', 'jenis' => 'Sepur lurus / dinas rangkaian', 'sort_order' => 6],
        ];

        foreach ($tracks as $t) {
            Track::query()->updateOrCreate(['code' => $t['code']], $t);
        }
    }
}
