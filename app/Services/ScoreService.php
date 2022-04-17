<?php


namespace App\Services;


use App\Models\SimulatedBet;

class ScoreService
{
    public function changeWonStatus(SimulatedBet $simulatedBet, $homeTeamSpread): SimulatedBet
    {
        $sharpLine = $simulatedBet->sharpLine()->first();
        $sharpHomeTeamSpread = $sharpLine['homeTeamSpread'];
        $nonSharpLine = $simulatedBet->nonSharpLine()->first();
        $nonSharpHomeTeamSpread = $nonSharpLine['awayTeamSpread'];
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


}
