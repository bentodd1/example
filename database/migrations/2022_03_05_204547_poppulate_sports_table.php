<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use \App\Models\Sport;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $response = Http::accept('application/json')->get('https://api.the-odds-api.com/v4/sports?apiKey=1a12221aff5a1654bb760995fdfea015');
        $array = $response->json();
        $sport = new Sport();
        $sport->
        $this->alert($array[0]["id"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
