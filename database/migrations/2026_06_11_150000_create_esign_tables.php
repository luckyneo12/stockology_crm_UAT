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
        if (!Schema::hasTable('esign_templates')) {
            Schema::create('esign_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('pdf_url');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('esign_template_fields')) {
            Schema::create('esign_template_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('esign_template_id')->constrained('esign_templates')->onDelete('cascade');
                $table->string('field_key');      // e.g. 'full_name', 'pan_number', 'signature_box'
                $table->string('label');          // e.g. 'Full Name'
                $table->string('type');           // 'text', 'signature'
                $table->integer('page_num');      // 1-indexed page number
                $table->float('x_coordinate');    // Left position (X)
                $table->float('y_coordinate');    // Bottom position (Y)
                $table->float('width')->default(150);
                $table->float('height')->default(50);
                $table->timestamps();
            });
        }

        // Add kyc_status and signed_doc_path columns to leads table if they don't exist
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'kyc_status')) {
                $table->string('kyc_status')->nullable()->after('stage_id');
            }
            if (!Schema::hasColumn('leads', 'signed_doc_path')) {
                $table->string('signed_doc_path')->nullable()->after('kyc_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esign_template_fields');
        Schema::dropIfExists('esign_templates');
        
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'kyc_status')) {
                $table->dropColumn('kyc_status');
            }
            if (Schema::hasColumn('leads', 'signed_doc_path')) {
                $table->dropColumn('signed_doc_path');
            }
        });
    }
};
