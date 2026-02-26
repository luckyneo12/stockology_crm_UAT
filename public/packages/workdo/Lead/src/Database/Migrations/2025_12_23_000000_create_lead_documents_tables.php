<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('lead_documents'))
        {
            Schema::create('lead_documents', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('stage_id')->nullable(); // Visible starting from this stage
                $table->boolean('is_required')->default(0);
                $table->integer('workspace_id');
                $table->integer('created_by');
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('lead_document_files'))
        {
            Schema::create('lead_document_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('document_id'); // Link to lead_documents
                $table->string('file_name');
                $table->string('file_path');
                $table->timestamps();
                
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
                $table->foreign('document_id')->references('id')->on('lead_documents')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_document_files');
        Schema::dropIfExists('lead_documents');
    }
};
