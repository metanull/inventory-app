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
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timeline_id');
            $table->string('internal_name');
            $table->integer('year_from');
            $table->integer('year_to');
            $table->integer('year_from_ah')->nullable();
            $table->integer('year_to_ah')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('display_order');
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->foreign('timeline_id')->references('id')->on('timelines')->onDelete('cascade');
            $table->index(['timeline_id', 'display_order']);
            $table->index(['timeline_id', 'year_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
