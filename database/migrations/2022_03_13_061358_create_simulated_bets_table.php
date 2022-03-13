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
        Schema::create('simulated_bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sharpBookId');
            $table->index('sharpBookId');
            $table->foreign('sharpBookId')->references('id')->on('casinos')->onDelete('cascade');

            $table->unsignedBigInteger('nonSharpBookId');
            $table->index('nonSharpBookId');
            $table->foreign('nonSharpBookId')->references('id')->on('casinos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulated_bets');
    }
};
