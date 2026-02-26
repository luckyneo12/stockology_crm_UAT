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
        // Create messenger_groups table if not exists
        if (!Schema::hasTable('messenger_groups')) {
            Schema::create('messenger_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('avatar')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace_id')->nullable();
                $table->timestamps();
                
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Create messenger_group_members table if not exists
        if (!Schema::hasTable('messenger_group_members')) {
            Schema::create('messenger_group_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('role', ['admin', 'member'])->default('member');
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamps();
                
                $table->foreign('group_id')->references('id')->on('messenger_groups')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['group_id', 'user_id']);
            });
        }

        // Add reply_to column to messages table
        Schema::table('user_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('user_messages', 'reply_to')) {
                $table->unsignedBigInteger('reply_to')->nullable()->after('attachment');
                $table->foreign('reply_to')->references('id')->on('user_messages')->onDelete('set null');
            }
        });

        // Add group_id column to messages table for group messages
        Schema::table('user_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('user_messages', 'group_id')) {
                $table->unsignedBigInteger('group_id')->nullable()->after('to_id');
                $table->foreign('group_id')->references('id')->on('messenger_groups')->onDelete('cascade');
            }
        });

        // Add last_seen and is_online columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_seen')) {
                $table->timestamp('last_seen')->nullable()->after('active_status');
            }
            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('last_seen');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_messages', function (Blueprint $table) {
            if (Schema::hasColumn('user_messages', 'reply_to')) {
                $table->dropForeign(['reply_to']);
                $table->dropColumn('reply_to');
            }
            if (Schema::hasColumn('user_messages', 'group_id')) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_seen')) {
                $table->dropColumn('last_seen');
            }
            if (Schema::hasColumn('users', 'is_online')) {
                $table->dropColumn('is_online');
            }
        });

        Schema::dropIfExists('messenger_group_members');
        Schema::dropIfExists('messenger_groups');
    }
};
