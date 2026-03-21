<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('signal_id');
                $table->decimal('target', 12, 2)->nullable();
                $table->decimal('stoploss', 12, 2)->nullable();
                $table->integer('quantity')->nullable();
                $table->text('note')->nullable();
                $table->integer('created_by')->default(0);
                $table->timestamps();

                $table->foreign('signal_id')->references('id')->on('stock_signals')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
