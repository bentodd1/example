<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * apiKey
    sportKey FK to sports
    commenceTime
    homeTeam
    awayTeam
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sportId');
            $table->string('apiKey');
            $table->index('sportId');
            $table->foreign('sportId')->references('id')->on('sports')->onDelete('cascade');
            $table->dateTimeTz('commenceTime');
            $table->string('homeTeam');
            $table->string('awayTeam');
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
        Schema::dropIfExists('games');
    }
};
