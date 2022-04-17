<?php

namespace Tests\Unit;

use App\Models\Score;
use App\Models\Sport;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\SimulatedBet;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Services\ScoreService;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class ScoreServiceTest extends TestCase
{
   // use RefreshDatabase;

    public function test_change_won_status(){
        $scoreService = new ScoreService();
        $sport = Sport::where('key','basketball_nba')->first();

        $game = new Game(['sportId' => $sport['id'], 'apiKey' => 'test',
            'commenceTime' => '2022-03-07 23:07:01',
            'homeTeam' =>'Flyer','awayTeam' => 'Penguin' ]);

        $game->save();

        // betMgm has key 4
        // draftKings is 1

        $sharpBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => 4, 'homeTeamSpread' => -5, 'awayTeamSpread' => 5, 'isCurrent' => true]);
        $nonSharpBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => 1, 'homeTeamSpread' => -3, 'awayTeamSpread' => 3, 'isCurrent' => true]);

        $sharpBettingLine->save();
        $nonSharpBettingLine->save();

        $simulatedBet = new SimulatedBet(['sharpBettingLineId' =>$sharpBettingLine['id'], 'nonSharpBettingLineId' => $nonSharpBettingLine['id']]);
        $simulatedBet->save();

        $score = new Score(['gameId' => $game['id'],
            'sportId' => $sport['id'],
            'homeTeamScore' => 5,
            'awayTeamScore' => 1,
            'lastUpdated' => '2022-03-07 23:07:01',
            'apiId' => 'test']);
        $score->save();

        $homeTeamSpread = $scoreService->calculateHomeTeamSpread($score);

        $simulatedBet = $scoreService->changeWonStatus($simulatedBet, $homeTeamSpread);
        //$simulatedBet = $scoreService->retroMatchBet($simulatedBet);
        $this->assertEquals(1 , $simulatedBet['won']);

        $homeTeamSpread = -2;
        $simulatedBet = $scoreService->changeWonStatus($simulatedBet, $homeTeamSpread);
        $this->assertEquals(0, $simulatedBet['won']);



    }

}
