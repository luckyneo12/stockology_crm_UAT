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
        if(!Schema::hasTable('lead_sections'))
        {
            Schema::create('lead_sections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('order')->default(0);
                $table->integer('columns')->default(3); // 1, 2, 3, 4
                $table->integer('workspace_id');
                $table->boolean('is_system')->default(0); // If true, cannot be deleted
                $table->timestamps();
            });
        }

        if(Schema::hasTable('lead_custom_fields'))
        {
            Schema::table('lead_custom_fields', function (Blueprint $table) {
                // Check if columns exist before adding them to prevent errors on re-run
                if (!Schema::hasColumn('lead_custom_fields', 'section_id')) {
                    $table->unsignedBigInteger('section_id')->nullable()->after('id');
                    // We can't strictly enforce FK yet because existing fields have no section
                    // $table->foreign('section_id')->references('id')->on('lead_sections')->onDelete('cascade');
                }
                if (!Schema::hasColumn('lead_custom_fields', 'width')) {
                    $table->integer('width')->default(1)->after('type'); // Column span (1-4)
                }
                if (!Schema::hasColumn('lead_custom_fields', 'is_system')) {
                    $table->boolean('is_system')->default(0)->after('is_required'); // If true, cannot be deleted
                }
                 if (!Schema::hasColumn('lead_custom_fields', 'system_field_id')) {
                    $table->string('system_field_id')->nullable()->after('is_system'); // To map to specific internal system fields (e.g. 'email', 'phone')
                }
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
        if(Schema::hasTable('lead_custom_fields'))
        {
             Schema::table('lead_custom_fields', function (Blueprint $table) {
                 $table->dropColumn(['section_id', 'width', 'is_system', 'system_field_id']);
             });
        }
        Schema::dropIfExists('lead_sections');
    }
};
