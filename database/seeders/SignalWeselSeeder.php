<?php

namespace Database\Seeders;

use App\Models\Signal;
use App\Models\Track;
use App\Models\Wesel;
use Illuminate\Database\Seeder;

class SignalWeselSeeder extends Seeder
{
    /**
     * Denah disederhanakan dari "Gambar Emplasemen Stasiun SGU.pdf" (Sintelis Daop 8, Juni 2017):
     * 6 jalur (I-VI) sejajar, wesel di kedua ujung (throat) mengumpul ke arah
     * Wonokromo (barat) dan ke arah Sidotopo/Surabaya Kota (timur).
     * Kode sinyal & wesel di bawah ini adalah representasi umum yang bisa
     * disesuaikan lagi lewat panel admin agar sama persis dengan gambar asli.
     */
    private array $layout = [
        ['code' => 'I',   'y' => 90,  'sinyal_barat' => '74',  'sinyal_timur' => '51', 'wesel_barat' => '212T', 'wesel_timur' => '274T'],
        ['code' => 'II',  'y' => 160, 'sinyal_barat' => '72',  'sinyal_timur' => '52', 'wesel_barat' => '216T', 'wesel_timur' => '277T'],
        ['code' => 'III', 'y' => 230, 'sinyal_barat' => '71',  'sinyal_timur' => '53', 'wesel_barat' => '219T', 'wesel_timur' => '279T'],
        ['code' => 'IV',  'y' => 300, 'sinyal_barat' => '73',  'sinyal_timur' => '54', 'wesel_barat' => '225T', 'wesel_timur' => '281T'],
        ['code' => 'V',   'y' => 370, 'sinyal_barat' => '78',  'sinyal_timur' => '55', 'wesel_barat' => '231T', 'wesel_timur' => '292T'],
        ['code' => 'VI',  'y' => 440, 'sinyal_barat' => '70',  'sinyal_timur' => '68', 'wesel_barat' => '260T', 'wesel_timur' => '332T'],
    ];

    public function run(): void
    {
        foreach ($this->layout as $row) {
            $track = Track::where('code', $row['code'])->first();
            if (! $track) {
                continue;
            }

            Signal::query()->updateOrCreate(
                ['code' => $row['sinyal_barat'], 'side' => 'barat'],
                [
                    'track_id' => $track->id,
                    'jenis' => 'masuk',
                    'pos_x' => 170,
                    'pos_y' => $row['y'],
                    'keterangan' => "Sinyal masuk arah Wonokromo untuk {$track->name}",
                ]
            );

            Signal::query()->updateOrCreate(
                ['code' => $row['sinyal_timur'], 'side' => 'timur'],
                [
                    'track_id' => $track->id,
                    'jenis' => 'masuk',
                    'pos_x' => 1030,
                    'pos_y' => $row['y'],
                    'keterangan' => "Sinyal masuk arah Sidotopo/Surabaya Kota untuk {$track->name}",
                ]
            );

            Wesel::query()->updateOrCreate(
                ['code' => $row['wesel_barat'], 'side' => 'barat'],
                [
                    'track_from_id' => $track->id,
                    'track_to_id' => $track->id,
                    'pos_x' => 280,
                    'pos_y' => $row['y'],
                    'keterangan' => "Wesel throat barat untuk {$track->name}",
                ]
            );

            Wesel::query()->updateOrCreate(
                ['code' => $row['wesel_timur'], 'side' => 'timur'],
                [
                    'track_from_id' => $track->id,
                    'track_to_id' => $track->id,
                    'pos_x' => 920,
                    'pos_y' => $row['y'],
                    'keterangan' => "Wesel throat timur untuk {$track->name}",
                ]
            );
        }
    }
}
