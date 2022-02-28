<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Casino FK
    Game FK
    createdDate
    endDate
    Home team spread
    Away team spread

     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_betting_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('casinoId');
            $table->unsignedBigInteger('gameId');
            $table->index('casinoId');
            $table->index('gameId');
            $table->foreign('casinoId')->references('id')->on('casinos')->onDelete('cascade');
            $table->foreign('gameId')->references('id')->on('games')->onDelete('cascade');
            $table->float('homeTeamSpread');
            $table->float('awayTeamSpread');
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
        Schema::dropIfExists('game_betting_lines');
    }
};
