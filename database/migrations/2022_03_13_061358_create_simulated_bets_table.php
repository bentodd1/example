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
            $table->unsignedBigInteger('sharpBettingLineId');
            $table->index('sharpBettingLineId');
            $table->foreign('sharpBettingLineId')->references('id')->on('game_betting_lines')->onDelete('cascade');

            $table->unsignedBigInteger('nonSharpBettingLineId');
            $table->index('nonSharpBettingLineId');
            $table->foreign('nonSharpBettingLineId')->references('id')->on('game_betting_lines')->onDelete('cascade');
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
