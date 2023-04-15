<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\SimulatedBet;
use App\Models\Sport;

class HistoricalLinesService
{

    public static function keyGamesById($data): array {
        $keyedGames = [];
        foreach($data as $game){
            if(isset($game['id'])) {
                $keyedGames[$game['id']] = $game;
            }
        }
        return $keyedGames;
    }

    public static function compareTwoLines(array $game1, array $game2, string $pinnacleUrl, string $draftKingsUrl)
    {
        if (isset($game1['bookmakers']) && $game2['bookmakers']) {
            $game1Bookmakers = $game1['bookmakers'];
            $homeTeam = $game1['home_team'];
            $awayTeam = $game1['away_team'];
            $commenceTime = $game1['commence_time'];
            $sport = Sport::where('key', $game1['sport_key'])->first();

            if ($homeTeam != $game2['home_team']) {
                echo 'error';
            }


            $games2Bookmakers = $game2['bookmakers'];
            if (isset($game1Bookmakers[0]['markets']) && isset($games2Bookmakers[0]['markets'])) {
                $markets1 = $game1Bookmakers[0]['markets'];
                $markets2 = $games2Bookmakers[0]['markets'];
                $market1 = self::getSpreadFromMarkets($markets1);
                $market2 = self::getSpreadFromMarkets($markets2);

                if (isset($market1['outcomes']) && isset($market2['outcomes'])) {
                    foreach ($market1['outcomes'] as $outcome) {
                        if ($outcome['name'] == $homeTeam) {
                            $outcome1 = $outcome;
                        }
                    }
                    foreach ($market2['outcomes'] as $outcome) {
                        if ($outcome['name'] == $homeTeam) {
                            $outcome2 = $outcome;
                        }

                    }

                    if (isset($outcome1['point']) && isset($outcome2['point'])) {
                        $game1Line = $outcome1['point'];
                        $game2Line = $outcome2['point'];
                        if (abs($game1Line - $game2Line) > 1.4) {
                            $datetime = str_replace('Z', '', $commenceTime);
                            $game = new Game(['sportId' => $sport['id'], 'apiKey' => $game1['id'], 'homeTeam' => $homeTeam, 'awayTeam' => $awayTeam, 'commenceTime' => $datetime]);
                            $game->save();
                            echo "Different";
                            echo "line 1 $game1Line line 2 $game2Line";
                            // Pinnacle is first
                            // Draftkings is second

                            //TODO actually use the casino that was passed to this
                            $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => 8, 'homeTeamSpread' => $game1Line, 'awayTeamSpread' => -$game1Line, 'isCurrent' => true, 'source' => $pinnacleUrl]);
                            $gameBettingLine->save();
                            $gameBettingLine2 = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => 1, 'homeTeamSpread' => $game2Line, 'awayTeamSpread' => -$game2Line, 'isCurrent' => true, 'source' => $draftKingsUrl]);
                            $gameBettingLine2->save();
                            $simulatedBet = new SimulatedBet(['sharpBettingLineId' => $gameBettingLine['id'], 'nonSharpBettingLineId' => $gameBettingLine2['id']]);
                            $simulatedBet->save();

                            $score = HistoricalScoreService::extractScoreFromGame($game);

                            if ($score) {
                                $homeScore = $score->homeTeamScore;
                                $awayScore = $score->awayTeamScore;
                                $result = self::determineIfLine1IsMoreAccurate($game1Line, $game2Line, $homeScore, $awayScore);
                                if ($result) {
                                    echo "Pinnacle wins \n";
                                    $simulatedBet->won = 1;
                                } else {
                                    echo "Pinnacle loses \n";
                                    $simulatedBet->won = 0;
                                }
                                $simulatedBet->scoreId = $score->id;
                                $simulatedBet->save();
                            } else {
                                echo "Can not find score";
                            }
                        }
                    }
                }
            }

        }

    }

    public static function getSpreadFromMarkets(array $markets ){
        foreach ($markets as $market) {
            if($market['key'] == 'spreads'){
                return $market;
            }
        }
        return null;
    }

    public static function determineIfLine1IsMoreAccurate($spread1, $spread2, $home_score, $away_score):bool{
        $actualScore = $away_score - $home_score;

        // Calculate the differences between the predicted and actual scores for each spread
        $diff1 = abs($spread1 - $actualScore) ;
        $diff2 = abs($spread2 - $actualScore) ;

        // Determine which spread was more accurate based on the differences
        if ($diff1 <= $diff2) {
            return true;
        } else {
            return false;
        }

    }





}
