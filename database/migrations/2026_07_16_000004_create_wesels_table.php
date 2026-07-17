<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wesels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->comment('Nomor wesel, mis. 201T');
            $table->foreignId('track_from_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->foreignId('track_to_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->enum('side', ['barat', 'timur'])->default('barat');
            $table->decimal('posisi_km', 6, 3)->nullable();
            $table->unsignedInteger('pos_x')->nullable();
            $table->unsignedInteger('pos_y')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['code', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wesels');
    }
};
