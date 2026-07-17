<?php

namespace App\Http\Controllers;

use App\Models\TrainSchedule;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function index(Request $request)
    {
        $availableDates = TrainSchedule::query()
            ->selectRaw('DISTINCT tanggal')
            ->orderBy('tanggal')
            ->pluck('tanggal')
            ->map(fn ($d) => $d->format('Y-m-d'));

        $tanggal = $request->query('tanggal', $availableDates->last() ?? now()->format('Y-m-d'));

        return view('simulation.index', [
            'tanggal' => $tanggal,
            'availableDates' => $availableDates,
        ]);
    }
}
