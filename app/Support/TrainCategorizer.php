<?php

namespace App\Support;

class TrainCategorizer
{
    /**
     * Menebak kategori KA berdasarkan nama pada jadwal.
     * Aturan sederhana berbasis kata kunci umum yang dipakai KAI di dokumen jadwal.
     */
    public static function classify(string $nama): string
    {
        $n = mb_strtolower($nama);

        if (str_contains($n, 'commuter line') || str_contains($n, 'lokal')) {
            return 'komuter';
        }

        if (
            str_contains($n, 'dinas rangkaian') ||
            str_contains($n, 'langsir') ||
            str_contains($n, 'kirim lok') ||
            str_contains($n, 'kirim rangkaian') ||
            str_contains($n, 'dipo')
        ) {
            return 'dinas';
        }

        if (
            str_contains($n, 'tanker') ||
            str_contains($n, 'babaranjang') ||
            str_contains($n, 'peti kemas') ||
            str_contains($n, 'container') ||
            str_contains($n, 'barang')
        ) {
            return 'barang';
        }

        // Nama KA penumpang jarak jauh/menengah (Pasundan, Bima, Turangga, Sancaka, dst.)
        return 'penumpang';
    }
}
