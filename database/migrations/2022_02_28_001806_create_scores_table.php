<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * "id": "e912304de2b2ce35b473ce2ecd3d1502",
    "sport_key": "americanfootball_nfl",
    "sport_title": "NFL",
    "commence_time": "2020-01-02T23:10:00Z",
    "completed": true,
    "home_team": "Houston Texans",
    "away_team": "Kansas City Chiefs",
    "scores": [
    {
    "name": "Houston Texans",
    "score": "20"
    },
    {
    "name": "Kansas City Chiefs",
    "score": "34"
    }
    ],
    "last_update": "2020-01-03T01:10:00Z"
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->string('apiId');

            $table->unsignedBigInteger('sportId');
            $table->index('sportId');
            $table->foreign('sportId')->references('id')->on('sports')->onDelete('cascade');

            $table->unsignedBigInteger('gameId');
            $table->index('gameId');
            $table->foreign('gameId')->references('id')->on('games')->onDelete('cascade');
            $table->float('homeTeamScore');
            $table->float('awayTeamScore');
            $table->time('lastUpdated');
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
        Schema::dropIfExists('scores');
    }
};
