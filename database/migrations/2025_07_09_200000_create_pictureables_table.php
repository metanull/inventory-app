<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pictureables', function (Blueprint $table) {
            $table->uuid('picture_id');
            $table->uuid('pictureable_id');
            $table->string('pictureable_type'); // Theme or Subtheme
            $table->integer('order')->nullable(); // Optional: for ordering pictures
            $table->timestamps();

            $table->primary(['picture_id', 'pictureable_id', 'pictureable_type'], 'pictureables_pk');
            $table->foreign('picture_id')->references('id')->on('pictures')->onDelete('cascade');
            // No foreign key for pictureable_id/type (polymorphic)
            $table->index(['pictureable_id', 'pictureable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pictureables');
    }
};
