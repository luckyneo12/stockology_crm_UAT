<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('stock_signals')) {
            Schema::create('stock_signals', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('symbol')->nullable();          // NSE symbol e.g. RELIANCE
                $table->string('exchange')->default('NSE');    // NSE / BSE / MCX
                $table->unsignedBigInteger('category_id')->nullable();
                $table->enum('type', ['buy', 'sell'])->default('buy');
                $table->decimal('buy_price_min', 12, 2)->nullable();
                $table->decimal('buy_price_max', 12, 2)->nullable();
                $table->decimal('target', 12, 2)->nullable();
                $table->decimal('stoploss', 12, 2)->nullable();
                $table->integer('quantity')->nullable();
                $table->decimal('min_amount', 12, 2)->nullable();
                $table->string('hold_duration')->nullable();   // e.g. "22 Day", "1 Month"
                $table->text('description')->nullable();
                $table->date('date');
                $table->enum('status', ['live', 'closed', 'pending'])->default('live');
                $table->decimal('exit_price', 12, 2)->nullable();
                $table->timestamp('exit_at')->nullable();
                $table->integer('workspace')->nullable();
                $table->integer('created_by')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stock_signals');
    }
};
