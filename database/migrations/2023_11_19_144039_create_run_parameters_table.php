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
        Schema::create('run_parameters', function (Blueprint $table) {
            $table->id();
            $table->date('runForDate');
            $table->integer('populationSize');
            $table->integer('maxIterations');
            $table->double('alpha');
            $table->double('gamma');
            $table->double('runTime');
            $table->bigInteger('movements');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_parameters');
    }
};
