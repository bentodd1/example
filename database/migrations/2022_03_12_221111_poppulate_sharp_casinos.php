<?php

use Illuminate\Database\Migrations\Migration;
use \App\Models\Casino;
use \App\Models\SharpCasino;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $casinos = Casino::whereIn('key',['sugarhouse', 'betrivers', 'betmgm'])->get();
        foreach ($casinos as $casino) {
        $casinoId = $casino['id'];
        $apiKey = $casino['key'];
            $sharpCasino = new SharpCasino(['casinoId' => $casinoId,'apiKey' => $apiKey]);
            $sharpCasino->save();
        }
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
