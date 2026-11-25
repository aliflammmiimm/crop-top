<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_histories', function (Blueprint $table): void {
            $table->id();
            $table->double('n');
            $table->double('p');
            $table->double('k');
            $table->double('ph');
            $table->double('latitude');
            $table->double('longitude');
            $table->double('temperature');
            $table->double('humidity');
            $table->double('rainfall');
            $table->string('top1_wp_kode', 10);
            $table->string('top1_wp_label', 50);
            $table->string('top1_wp_nama_id', 100);
            $table->double('top1_wp_score');
            $table->double('top1_wp_ln_p');
            $table->string('top1_topsis_kode', 10);
            $table->string('top1_topsis_label', 50);
            $table->string('top1_topsis_nama_id', 100);
            $table->double('top1_topsis_score');
            $table->string('agreement_status', 20);
            $table->json('range_warnings')->nullable();
            $table->json('results_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_histories');
    }
};
