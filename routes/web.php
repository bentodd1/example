<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\SimulatedBet;
use App\Models\Score;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Display All Bets
 */
Route::get('/', function () {
    $simulatedBets = SimulatedBet::orderBy('created_at', 'desc')->get();
    $scores = Score::orderBy('created_at', 'asc')->get();
    $scoreSize = count($scores) ;
    $winCount = 0;
    $lossCount = 0;
    foreach ($simulatedBets as $simulatedBet)
    {
        if(isset($simulatedBet['won'])) {
            if ($simulatedBet['won']) {
                $winCount++;
            } else if ($simulatedBet['won'] == 0) {
                $lossCount++;
            }
        }
    }
    $winRate = 100.00;
    if($lossCount >0) {
        $winRate = $winCount/($winCount + $lossCount);
    }

    return view('tasks', [
        'scoreSize' => $scoreSize,
        'winCount' => $winCount,
        'lossCount' => $lossCount,
        'winrate' => $winRate,
        'simulatedBets' => $simulatedBets->take(30)
    ]);
});

