<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedInteger('urutan')->default(0)
                ->comment('Urutan baris asli pada GAPEKA/jadwal, untuk pengurutan saat jam sama');
            $table->foreignId('train_id')->nullable()->constrained('trains')->nullOnDelete();
            $table->string('no_ka', 30);
            $table->string('nama_ka');
            $table->foreignId('relasi_asal_id')->nullable()->constrained('stations')->nullOnDelete();
            $table->foreignId('relasi_tujuan_id')->nullable()->constrained('stations')->nullOnDelete();
            $table->string('relasi_raw', 60)->nullable()->comment('Teks relasi asli, mis. SGU-SB');
            $table->time('jam_datang')->nullable();
            $table->string('jam_datang_ket', 20)->nullable()->comment('Keterangan non-jam, mis. Ls (langsung)');
            $table->time('jam_berangkat')->nullable();
            $table->string('jam_berangkat_ket', 20)->nullable();
            $table->foreignId('track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_schedules');
    }
};
