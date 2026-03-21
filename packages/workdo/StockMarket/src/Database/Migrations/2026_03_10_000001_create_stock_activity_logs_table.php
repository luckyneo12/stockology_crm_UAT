<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stock_activity_logs')) {
            Schema::create('stock_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('signal_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action'); // e.g. Created, Updated, Closed, Deleted
                $table->text('details')->nullable(); // JSON or text details of what changed
                $table->unsignedBigInteger('workspace_id')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('stock_activity_logs');
    }
};
