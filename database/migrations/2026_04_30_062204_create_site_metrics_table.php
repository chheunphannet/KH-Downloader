<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_metrics', function (Blueprint $table) {
            $table->id('site_metrics_id');
            $table->timestamp('updated_at')->useCurrent();
            $table->string('site_name', 50);
            $table->integer('total_processes')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_metrics');
    }
};
