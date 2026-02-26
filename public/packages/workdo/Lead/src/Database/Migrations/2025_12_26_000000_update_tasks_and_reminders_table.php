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
        // 1. Create Reminders Table
        if(!Schema::hasTable('reminders'))
        {
            Schema::create('reminders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // Assigned User
                $table->morphs('remindable');
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('remind_at');
                $table->string('type')->default('follow_up'); // call, message, follow_up
                $table->boolean('is_sent')->default(false);
                $table->integer('workspace_id')->default(0);
                $table->integer('created_by')->default(0);
                $table->timestamps();
            });
        }

        // 2. Enhance Lead Tasks
        if(Schema::hasTable('lead_tasks'))
        {
            Schema::table('lead_tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_tasks', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('lead_id');
                }
                if (!Schema::hasColumn('lead_tasks', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
            });
        }

        // 3. Enhance Deal Tasks
        if(Schema::hasTable('deal_tasks'))
        {
            Schema::table('deal_tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('deal_tasks', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('deal_id');
                }
                if (!Schema::hasColumn('deal_tasks', 'description')) {
                    $table->text('description')->nullable()->after('name');
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
        Schema::dropIfExists('reminders');
        
        if(Schema::hasTable('lead_tasks'))
        {
            Schema::table('lead_tasks', function (Blueprint $table) {
                $table->dropColumn(['user_id', 'description']);
            });
        }

        if(Schema::hasTable('deal_tasks'))
        {
            Schema::table('deal_tasks', function (Blueprint $table) {
                $table->dropColumn(['user_id', 'description']);
            });
        }
    }
};
