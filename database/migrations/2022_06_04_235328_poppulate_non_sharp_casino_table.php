<?php

use Illuminate\Database\Migrations\Migration;
use \App\Models\Casino;
use \App\Models\NonSharpCasino;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $casinos = Casino::whereIn('key', ['draftkings',
            'fanduel',
            'foxbet',
            'betmgm',
            'sugarhouse',
            'twinspires',
            'barstool',
            'wynnbet',
            'williamhill_us',
            'pointsbetus'])->get();
        foreach ($casinos as $casino) {
            $casinoId = $casino['id'];
            $apiKey = $casino['key'];
            $sharpCasino = new NonSharpCasino(['casinoId' => $casinoId, 'apiKey' => $apiKey]);
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
