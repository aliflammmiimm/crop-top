<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('label', 50)->unique();
            $table->string('nama_id', 100);
            $table->json('mu');
            $table->json('sd');
            $table->string('image_path', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
