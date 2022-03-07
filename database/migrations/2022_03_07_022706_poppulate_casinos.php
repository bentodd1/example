<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use  App\Models\Casino;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "key": "draftkings",
    "title": "DraftKings",

    "key": "fanduel",
    "title": "FanDuel",

    "key": "foxbet",
    "title": "FOX Bet",

    "key": "betmgm",
    "title": "BetMGM",

    "key": "sugarhouse",
    "title": "SugarHouse",

    "key": "twinspires",
    "title": "TwinSpires",

    "key": "barstool",
    "title": "Barstool Sportsbook",

    "key": "unibet",
    "title": "Unibet",
     * @return void
     */
    public function up()
    {
        $casinosArray = [
            ["key" => "draftkings",
                "title" => "DraftKings"],
            ["key" => "fanduel",
                "title" => "FanDuel"],
            ["key" => "foxbet",
                "title" => "FOX Bet"],
            ["key" => "betmgm",
                "title" => "BetMGM"],
            ["key" => "sugarhouse",
                "title" => "SugarHouse"],
            ["key" => "twinspires",
                "title" => "TwinSpires"],
            ["key" => "barstool",
                "title" => "Barstool Sportsbook"]
        ];
        foreach ($casinosArray as $casino){
           $newCasino = new Casino(['key' => $casino['key'], 'title' =>$casino['title']]);
           $newCasino->save();
        }
        //
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
