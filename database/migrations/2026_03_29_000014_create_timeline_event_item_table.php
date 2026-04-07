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
        Schema::create('timeline_event_item', function (Blueprint $table) {
            $table->uuid('timeline_event_id');
            $table->uuid('item_id');
            $table->integer('display_order')->default(0);
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->primary(['timeline_event_id', 'item_id']);
            $table->foreign('timeline_event_id')->references('id')->on('timeline_events')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_event_item');
    }
};
