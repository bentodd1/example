<?php

namespace App\Services;

use App\Models\Score;

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

    public static function compareTwoLines(array $game1, array $game2, string $key, &$pinnacle_wins, &$draftkings_wins ){
        if(isset($game1['bookmakers']) && $game2['bookmakers']) {
            $game1Bookmakers = $game1['bookmakers'];
            $games2Bookmakers = $game2['bookmakers'];
            if(isset($game1Bookmakers[0]['markets']) && isset($games2Bookmakers[0]['markets']))
            {
                $markets1 = $game1Bookmakers[0]['markets'];
                $markets2 = $games2Bookmakers[0]['markets'];
                $market1 =  self::getSpreadFromMarkets($markets1);
                $market2 =  self::getSpreadFromMarkets($markets2);

                if(isset($market1['outcomes']) && isset($market2['outcomes']))
                {
                    $outcomes = $market1['outcomes'][0];
                    $outcomes2 = $market2['outcomes'][0];
                    if(isset($outcomes['point']) && isset($outcomes2['point']))
                    {
                        $game1Line = -$outcomes['point'];
                        $game2Line  = -$outcomes2['point'];
                        if(abs($game1Line - $game2Line) > 1.4) {
                            echo "Different";
                            echo "line 1 $game1Line line 2 $game2Line";
                            $score = Score::where(['apiId' => $key])->first();
                            if($score) {
                                $homeScore = $score['homeTeamScore'];
                                $awayScore = $score['awayTeamScore'];
                                $result = self::determineIfLine1IsMoreAccurate($game1Line, $game2Line, $homeScore, $awayScore);

                                if ($result) {
                                    $pinnacle_wins++;
                                } else {
                                    $draftkings_wins++;
                                }
                            }
                            else{
                                $homeTeam = $game1['home_team'];
                                $awayTeam = $game1['away_team'];
                                $commenceTime = $game1['commence_time'];
                                echo "Can not find score for $awayTeam at $homeTeam $commenceTime";
                                $myfile = fopen("lines2.txt", "a") or die("Unable to open file!");
                                $txt = "Home team $homeTeam away team is $awayTeam time is $commenceTime \n";
                                $text2 = "Pinnacle Line $game1Line Draft kings line $game2Line \n";
                                fwrite($myfile, $txt);
                                fwrite($myfile, $text2);
                                fclose($myfile);

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
