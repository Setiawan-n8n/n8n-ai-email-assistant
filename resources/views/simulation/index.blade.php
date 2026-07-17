@extends('layouts.app')

@section('title', 'Simulasi Perjalanan KA - Stasiun Surabaya Gubeng (SGU)')

@section('content')
<div class="app-shell">
    <header class="topbar">
        <div class="topbar-title">
            <span class="topbar-kicker">SRRL Project</span>
            <h1>Simulasi Perjalanan KA &mdash; Stasiun Surabaya Gubeng (SGU)</h1>
        </div>
        <div class="topbar-actions">
            <label class="field-inline">
                <span>Tanggal</span>
                <select id="tanggalSelect">
                    @forelse($availableDates as $d)
                        <option value="{{ $d }}" @selected($d === $tanggal)>{{ \Carbon\Carbon::parse($d)->translatedFormat('d F Y') }}</option>
                    @empty
                        <option value="{{ $tanggal }}">{{ $tanggal }}</option>
                    @endforelse
                </select>
            </label>
            <a href="{{ url('/admin') }}" class="btn btn-ghost" target="_blank" rel="noopener">Panel Admin &rarr;</a>
        </div>
    </header>

    <main class="stage">
        <section class="board-wrap">
            <div class="board-toolbar">
                <button id="btnPlay" class="btn btn-primary" type="button">&#9654; Mulai</button>
                <button id="btnReset" class="btn btn-ghost" type="button">&#8635; Ulangi</button>

                <label class="field-inline">
                    <span>Kecepatan</span>
                    <select id="speedSelect">
                        <option value="1">1x</option>
                        <option value="5">5x</option>
                        <option value="15">15x</option>
                        <option value="30" selected>30x</option>
                        <option value="60">60x</option>
                        <option value="120">120x</option>
                    </select>
                </label>

                <div class="clock" id="clockReadout">00:00</div>

                <input type="range" id="timeSlider" min="0" max="1439" value="300" step="1" class="time-slider">
            </div>

            <div class="board-canvas">
                <svg id="stationSvg" viewBox="0 0 1200 520" preserveAspectRatio="xMidYMid meet"></svg>
            </div>

            <div class="board-legend">
                <span class="legend-item"><i class="dot dot-penumpang"></i> Penumpang</span>
                <span class="legend-item"><i class="dot dot-komuter"></i> Komuter</span>
                <span class="legend-item"><i class="dot dot-barang"></i> Barang</span>
                <span class="legend-item"><i class="dot dot-dinas"></i> Dinas/Langsir</span>
                <span class="legend-item"><i class="sig sig-hijau"></i> Sinyal Aman</span>
                <span class="legend-item"><i class="sig sig-merah"></i> Sinyal Tidak Aman</span>
            </div>
        </section>

        <aside class="side-panel">
            <h2>Di Stasiun Sekarang</h2>
            <div id="currentTrains" class="train-list">
                <p class="muted">Menunggu simulasi berjalan&hellip;</p>
            </div>

            <h2>Jadwal Berikutnya</h2>
            <div id="upcomingTrains" class="train-list"></div>
        </aside>
    </main>
</div>
@endsection

@push('scripts')
<script>
    window.SIMULATION_CONFIG = {
        apiUrl: @json(url('/api/schedule')),
        tanggal: @json($tanggal),
    };
</script>
<script src="{{ asset('js/simulation.js') }}"></script>
@endpush
