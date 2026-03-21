<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('stock_notifications')) {
            Schema::create('stock_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('signal_id');
                $table->unsignedBigInteger('user_id');
                $table->boolean('is_read')->default(false);
                $table->string('type')->default('new_signal'); // new_signal, adjustment, closed
                $table->integer('workspace')->nullable();
                $table->timestamps();

                $table->foreign('signal_id')->references('id')->on('stock_signals')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stock_notifications');
    }
};
