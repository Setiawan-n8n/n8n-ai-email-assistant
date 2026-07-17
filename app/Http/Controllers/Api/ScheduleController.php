<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\Station;
use App\Models\Track;
use App\Models\TrainSchedule;
use App\Models\Wesel;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Mengembalikan seluruh data yang dibutuhkan halaman simulasi
     * untuk satu tanggal: jalur, sinyal, wesel, stasiun, dan jadwal KA.
     */
    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal');

        if (! $tanggal) {
            $tanggal = TrainSchedule::query()->max('tanggal');
        }

        $tracks = Track::query()->orderBy('sort_order')->get(['id', 'code', 'name', 'jenis']);

        $signals = Signal::query()->get(['id', 'code', 'track_id', 'side', 'jenis', 'pos_x', 'pos_y']);

        $wesels = Wesel::query()->get(['id', 'code', 'track_from_id', 'track_to_id', 'side', 'pos_x', 'pos_y']);

        $stations = Station::query()->get(['id', 'code', 'name', 'side', 'is_own_station']);

        $jadwal = TrainSchedule::query()
            ->with(['asal:id,code,name,side', 'tujuan:id,code,name,side', 'track:id,code,name', 'train:id,no_ka,nama,kategori'])
            ->when($tanggal, fn ($q) => $q->whereDate('tanggal', $tanggal))
            ->orderBy('urutan')
            ->get()
            ->map(function (TrainSchedule $s) {
                return [
                    'id' => $s->id,
                    'urutan' => $s->urutan,
                    'no_ka' => $s->no_ka,
                    'nama_ka' => $s->nama_ka,
                    'kategori' => $s->train?->kategori ?? 'lainnya',
                    'relasi_raw' => $s->relasi_raw,
                    'asal' => $s->asal ? ['code' => $s->asal->code, 'name' => $s->asal->name, 'side' => $s->asal->side] : null,
                    'tujuan' => $s->tujuan ? ['code' => $s->tujuan->code, 'name' => $s->tujuan->name, 'side' => $s->tujuan->side] : null,
                    'jam_datang' => optional($s->jam_datang)->format('H:i'),
                    'jam_datang_ket' => $s->jam_datang_ket,
                    'jam_berangkat' => optional($s->jam_berangkat)->format('H:i'),
                    'jam_berangkat_ket' => $s->jam_berangkat_ket,
                    'track' => $s->track ? ['code' => $s->track->code, 'name' => $s->track->name] : null,
                ];
            });

        return response()->json([
            'tanggal' => $tanggal,
            'tracks' => $tracks,
            'signals' => $signals,
            'wesels' => $wesels,
            'stations' => $stations,
            'jadwal' => $jadwal,
        ]);
    }
}
