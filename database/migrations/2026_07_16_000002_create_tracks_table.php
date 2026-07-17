<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique()->comment('I, II, III, IV, V, VI');
            $table->string('name');
            $table->string('jenis')->default('sepur lurus/badug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
