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
        if(!Schema::hasTable('user_activity_logs'))
        {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type')->default('App\Models\User');
                $table->string('activity_type'); // login, logout, view, create, edit, delete, etc.
                $table->string('module'); // users, leads, projects, etc.
                $table->text('description')->nullable();
                $table->string('url')->nullable();
                $table->string('method')->nullable(); // GET, POST, PUT, DELETE
                $table->string('ip_address');
                $table->string('user_agent')->nullable();
                $table->string('browser')->nullable();
                $table->string('browser_version')->nullable();
                $table->string('os')->nullable();
                $table->string('os_version')->nullable();
                $table->string('device_type')->nullable(); // desktop, mobile, tablet
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->json('request_data')->nullable(); // Store form data, parameters
                $table->json('response_data')->nullable(); // Store response data
                $table->integer('response_time_ms')->nullable(); // Response time in milliseconds
                $table->string('session_id')->nullable();
                $table->integer('workspace')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['user_id', 'created_at']);
                $table->index(['activity_type', 'created_at']);
                $table->index(['module', 'created_at']);
                $table->index(['ip_address', 'created_at']);
                $table->index(['workspace', 'created_at']);
                
                // Foreign key constraints
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
