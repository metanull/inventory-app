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
        Schema::dropIfExists('pictureables');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the pictureables table for rollback
        Schema::create('pictureables', function (Blueprint $table) {
            $table->uuid('picture_id');
            $table->uuidMorphs('pictureable');
            $table->integer('order')->default(0);
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->unique(['picture_id', 'pictureable_id', 'pictureable_type'], 'pictureables_unique');
            $table->index(['pictureable_id', 'pictureable_type']);
        });
    }
};
