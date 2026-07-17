<?php

namespace App\Console\Commands;

use App\Support\JadwalImporter;
use Illuminate\Console\Command;

class ImportJadwalKa extends Command
{
    /**
     * php artisan jadwal:import "storage/app/jadwal.xlsx" --tanggal=2026-07-15
     */
    protected $signature = 'jadwal:import
        {file : Path ke file .xlsx jadwal KA}
        {--tanggal= : Tanggal jadwal (YYYY-MM-DD), default hari ini}
        {--sheet=Sheet1 : Nama sheet}';

    protected $description = 'Import jadwal KA di Stasiun SGU dari file Excel (format: No, No KA, Relasi, Nama, DAT, BER, JALUR mulai kolom C, header di baris 8)';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        $tanggal = $this->option('tanggal') ?: now()->format('Y-m-d');

        $imported = JadwalImporter::importFromFile($file, $tanggal, $this->option('sheet'));

        $this->info("Berhasil import {$imported} baris jadwal untuk tanggal {$tanggal}.");

        return self::SUCCESS;
    }
}
