<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->foreignId('track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->enum('side', ['barat', 'timur'])->default('barat')
                ->comment('barat = arah Wonokromo, timur = arah Sidotopo/Surabaya Kota');
            $table->enum('jenis', ['masuk', 'keluar', 'langsir', 'blok'])->default('keluar');
            $table->decimal('posisi_km', 6, 3)->nullable();
            $table->unsignedInteger('pos_x')->nullable()->comment('posisi X pada denah SVG (0-1000)');
            $table->unsignedInteger('pos_y')->nullable()->comment('posisi Y pada denah SVG (0-500)');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['code', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
