<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('stock_categories')) {
            Schema::create('stock_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('equity'); // equity, fo, commodity, currency, index
                $table->integer('workspace')->nullable();
                $table->integer('created_by')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stock_categories');
    }
};
