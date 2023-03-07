<?php


namespace App\Services;


use App\Models\SimulatedBet;
use App\Models\Score;
use Illuminate\Support\Facades\Log;

class ScoreService
{

    public function changeWonStatus(SimulatedBet $simulatedBet, $homeTeamSpread): SimulatedBet
    {
        $sharpLine = $simulatedBet->originalLine()->first();
        $sharpHomeTeamSpread = $sharpLine['homeTeamSpread'];
        $nonSharpLine = $simulatedBet->movingLine()->first();
        $nonSharpHomeTeamSpread = $nonSharpLine['homeTeamSpread'];
        Log::debug("Sharphome team spread $sharpHomeTeamSpread");
        Log::debug("NonSharphome team spread $nonSharpHomeTeamSpread");


        if ($sharpHomeTeamSpread > $nonSharpHomeTeamSpread) {
            if ($homeTeamSpread > $nonSharpHomeTeamSpread) {
                $simulatedBet['won'] = 1;
            } else {
                $simulatedBet['won'] = 0;
            }
        }
        if ($sharpHomeTeamSpread < $nonSharpHomeTeamSpread) {
            if ($homeTeamSpread < $nonSharpHomeTeamSpread) {
                $simulatedBet['won'] = 1;
            } else {
                $simulatedBet['won'] = 0;
            }

        }
        $simulatedBet->save();
        return $simulatedBet;
    }

    public function retroMatchBet(SimulatedBet $simulatedBet): SimulatedBet{
        $sharpLine = $simulatedBet->originalLine()->first();
        $game = $sharpLine->game()->first();
        $score = Score::where(['apiId' => $game['apiKey']])->first();
        if($score) {
            $homeTeamSpread = $this->calculateHomeTeamSpread($score);
            $simulatedBet['scoreId'] = $score['id'];
            $this->changeWonStatus($simulatedBet, $homeTeamSpread);
        }
        return $simulatedBet;

    }

    public function calculateHomeTeamSpread(Score $score): float
    {
        $homeTeamScore = $score['homeTeamScore'];
        $awayTeamScore = $score['awayTeamScore'];
        return $awayTeamScore - $homeTeamScore;
    }




}
