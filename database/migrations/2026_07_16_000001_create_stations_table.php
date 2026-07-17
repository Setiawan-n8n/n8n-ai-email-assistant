<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->enum('side', ['barat', 'timur'])->default('barat')
                ->comment('Sisi emplasemen: barat = arah Wonokromo, timur = arah Sidotopo/Surabaya Kota');
            $table->boolean('is_own_station')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
