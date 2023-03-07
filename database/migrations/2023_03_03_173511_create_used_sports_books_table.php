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
        Schema::create('used_sports_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('casinoId');
            $table->string('apiKey');
            $table->index('casinoId');
            $table->foreign('casinoId')->references('id')->on('casinos')->onDelete('cascade');
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
        Schema::dropIfExists('used_sports_books');
    }
};
