<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trains', function (Blueprint $table) {
            $table->id();
            $table->string('no_ka', 30)->index();
            $table->string('nama');
            $table->enum('kategori', ['penumpang', 'barang', 'komuter', 'langsir', 'dinas', 'lainnya'])
                ->default('lainnya');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['no_ka', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trains');
    }
};
