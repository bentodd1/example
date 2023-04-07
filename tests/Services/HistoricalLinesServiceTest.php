<?php

namespace Tests\Services;

use App\Services\HistoricalLinesService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HistoricalLinesServiceTest extends TestCase
{

    public function testKeyGamesById(): array {
        $draftkings_response = Http::get('https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds-history/?apiKey=a1afa0961f51b8fef9289253c9dfb21f&bookmakers=draftkings&markets=h2h,spreads&oddsFormat=american&date=2021-01-01T12:00:00Z');
        $pinnacle_response = Http::get('https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds-history/?apiKey=a1afa0961f51b8fef9289253c9dfb21f&bookmakers=pinnacle&markets=h2h,spreads&oddsFormat=american&date=2021-01-01T12:00:00Z');
        $draftkings_odds = $draftkings_response->json();
        $pinnacle_odds = $pinnacle_response->json();
        $keyedDraftKings = HistoricalLinesService::keyGamesById($draftkings_odds['data']);
        $keyedPinnacle = HistoricalLinesService::keyGamesById($pinnacle_odds['data']);
        $keys = array_keys($keyedDraftKings);
//        foreach($keys as $key => $value) {
//            echo "$key is at $value";
//        }
        $this->assertNotEmpty($keyedDraftKings);
        $this->assertNotEmpty($keyedPinnacle);
        return['keyedP' => $keyedPinnacle, 'keyedD' => $keyedDraftKings];

    }

    /**
     * @depends testKeyGamesById
     */
    public function testCompareGames(array $gamesKeyed){
        $pinnacleGames = $gamesKeyed['keyedP'];
        $draftKingsGames = $gamesKeyed['keyedD'];
        foreach($pinnacleGames as $key => $game){
            if(isset($draftKingsGames[$key]))
            {
                HistoricalLinesService::compareTwoLines($pinnacleGames[$key],$draftKingsGames[$key], $key);
            }

        }
        $this->assertEquals('Hi', 'Hi');

    }

    /**
     * @test
     * @dataProvider myTestProvider
     */
    public function testLine1MoreAccurate($homeTeamScore, $awayTeamScore, $spread1, $spread2, $expected){
        $result = HistoricalLinesService::determineIfLine1IsMoreAccurate($spread1, $spread2,$homeTeamScore, $awayTeamScore );
        $this->assertEquals($expected, $result);

    }

    public function myTestProvider()
    {
        return [
            [80, 76, -2, -1, true],
            [80, 76, -1, -2, false],
            [76, 80, -2, -1, false],
            [76, 80, -1, -2, true],

            [80, 76, 1, -1, false],
            [80, 76, -1, 1, true],
            [76, 80, 1, -1, true],
            [76, 80, -1, 1, false],

            [80, 76, 1, 2, true],
            [80, 76, 2, 1, false],
            [76, 80, 1, 2, false],
            [76, 80, 2, 1, true],



        ];
    }
}
