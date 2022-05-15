<?php

namespace App\Services;

use App\Models\GameBettingLine;
use Carbon\Carbon;

class GetLinesService
{

    public function handleIncomingBetLine(?GameBettingLine $currentBettingLine, GameBettingLine $newBettingLine, $newLines = []): array
    {
        if (!$currentBettingLine) {
            $newBettingLine->save();
            $newLines[] = $newBettingLine;
        } else {
            if (($currentBettingLine['homeTeamSpread'] != $newBettingLine['homeTeamSpread'])) {

                $newBettingLine->save();
                $currentBettingLine['isCurrent'] = false;
                $currentBettingLine['expired_time'] = Carbon::now();
                $currentBettingLine->save();
                $newLines[] = $newBettingLine;
            }
        }
        return $newLines;
    }

    //findLineDiffs

    // remove active games from get

    // is line different

    // compare to other casino.


}
