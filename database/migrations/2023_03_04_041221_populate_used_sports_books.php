<?php

use App\Models\Casino;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $casinos = Casino::whereIn('key', ['draftkings',
            'fanduel',
            'betmgm',
            'barstool',
            'williamhill_us',
            'pointsbetus'])->get();
        foreach ($casinos as $casino) {
            $casinoId = $casino['id'];
            $apiKey = $casino['key'];
            $sharpCasino = new \App\Models\UsedSportsBooks(['casinoId' => $casinoId, 'apiKey' => $apiKey]);
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
