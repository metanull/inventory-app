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
        Schema::create('partner_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');

            // Core partner info
            $table->string('name');
            $table->text('description')->nullable();

            // Location/Address (embedded)
            $table->string('city_display')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('address_notes')->nullable();

            // Contact Information (semi-structured)
            $table->string('contact_name')->nullable();
            $table->string('contact_email_general')->nullable();
            $table->string('contact_email_press')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_website')->nullable();
            $table->text('contact_notes')->nullable();
            $table->json('contact_emails')->nullable();
            $table->json('contact_phones')->nullable();

            // Metadata
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            // Unique constraint
            $table->unique(['partner_id', 'language_id', 'context_id'], 'partner_translations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_translations');
    }
};
