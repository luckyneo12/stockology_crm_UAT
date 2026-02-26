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
        Schema::create('ekyc_ui_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->integer('step_number'); // Which KYC step this component belongs to
            $table->string('component_type', 50); // text_input, email, phone, otp, etc.
            $table->json('component_config'); // Field-specific settings
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->json('conditional_logic')->nullable(); // Show/hide based on other fields
            $table->json('validation_rules')->nullable(); // Custom validation
            $table->timestamps();
            
            // Indexes
            $table->index(['template_id', 'step_number']);
            $table->index('display_order');
            
            // Foreign key
            $table->foreign('template_id')->references('id')->on('ekyc_ui_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekyc_ui_components');
    }
};
