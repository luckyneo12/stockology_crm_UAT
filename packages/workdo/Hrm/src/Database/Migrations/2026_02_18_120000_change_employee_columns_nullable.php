<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('department_id')->nullable()->change();
            $table->integer('designation_id')->nullable()->change();
            $table->integer('branch_id')->nullable()->change();
            $table->string('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('department_id')->nullable(false)->change();
            $table->integer('designation_id')->nullable(false)->change();
            $table->integer('branch_id')->nullable(false)->change();
            $table->string('employee_id')->nullable(false)->change();
        });
    }
};
