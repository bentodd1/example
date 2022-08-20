<?php

namespace App\Services;

use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\NonSharpCasino;
use App\Models\SharpCasino;
use App\Models\SimulatedBet;
use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Sns\SnsClient;
use Carbon\Carbon;

class GetLinesService
{

    // Used to know whether or not to save a new betting.
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

    /**
     * Compares the new lines to the current sharp casino lines.
     * @param GameBettingLine[] $lines
     */
    public function findLineDiffs(array $lines)
    {
        // For now compare with a few casinos.
        $nonSharpCasinos = NonSharpCasino::all('casinoId')->toArray();
        $nonSharpCasinoIds = [];
        foreach ($nonSharpCasinos as $nonSharpCasino) {
            $nonSharpCasinoIds[] = $nonSharpCasino['casinoId'];
        }

        foreach ($lines as $line) {
            if (!in_array($line['casinoId'], $nonSharpCasinoIds)) {
                continue;
            }
            $gameId = $line['gameId'];
            $sharpCasinoIds = SharpCasino::all('casinoId');
            $sharpCasinoLines = GameBettingLine::where('gameId', $gameId)->where('isCurrent', true)->whereIn('casinoId', $sharpCasinoIds)->get();
            foreach ($sharpCasinoLines as $sharpCasinoLine) {
                $spreadMismatch = $this->getSpreadMismatch($line, $sharpCasinoLine);
                if ($spreadMismatch > 1.4) {
                    $this->handleSpreadMismatch($line, $sharpCasinoLine);
                }

            }

        }
    }

    public function hasSpreadMismatch(GameBettingLine $line, GameBettingLine $sharpBettingLine, float $minDifference = 1.4)
    {
        $homeSpread = $line['homeTeamSpread'];
        $awaySpread = $line['awayTeamSpread'];

        $homeCompareSpread = $sharpBettingLine['homeTeamSpread'];
        $awayCompareSpread = $sharpBettingLine['awayTeamSpread'];
        $homeDiff = $homeSpread - $homeCompareSpread;
        $awayDiff = $awaySpread - $awayCompareSpread;

        if (abs($homeDiff) > $minDifference || abs($awayDiff) > $minDifference) {
            return true;

        }
        return false;
    }

    public function getSpreadMismatch(GameBettingLine $line, GameBettingLine $sharpBettingLine): float
    {
        $homeSpread = $line['homeTeamSpread'];
        $homeCompareSpread = $sharpBettingLine['homeTeamSpread'];
        $homeDiff = $homeSpread - $homeCompareSpread;
        return abs($homeDiff);
    }


    public function sendTextMessage(string $message, string $phone)
    {
        $SnSclient = new SnsClient([
            'version' => '2010-03-31',
            'region' => 'us-east-1',
            'credentials' => new Credentials(
                env('AWS_KEY'),
                env('AWS_SECRET')

            )
        ]);

        try {
            $result = $SnSclient->publish([
                'Message' => $message,
                'PhoneNumber' => $phone,
            ]);
        } catch (AwsException $e) {
            error_log($e->getMessage());
        }
    }


    public function handleSpreadMismatch(GameBettingLine $nonSharpLine, GameBettingLine $sharpBettingLine)
    {
        $spreadMismatch = $sharpBettingLine['homeTeamSpread'] - $nonSharpLine['homeTeamSpread'];
        $game = Game::where('id', $nonSharpLine['gameId'])->first();
        $homeTeam = $game['homeTeam'];
        $awayTeam = $game['awayTeam'];
        $casino = Casino::where('id', $nonSharpLine['casinoId'])->first();
        $casinoKey = $casino['key'];
        $casino2 = Casino::where('id', $sharpBettingLine['casinoId'])->first();
        $casinoKey2 = $casino2['key'];

        $simulatedBet = new SimulatedBet(['sharpBettingLineId' => $sharpBettingLine['id'], 'nonSharpBettingLineId' => $nonSharpLine['id']]);
        $simulatedBet->save();
        $bettingSide  = $simulatedBet->getBettingSide();
        $bettingSideName = $homeTeam;
        $lineAmmount = $simulatedBet->nonSharpLine()->first()['homeTeamSpread'];
        if($bettingSide == 'awayTeam') {
            $bettingSideName = $awayTeam;
        }
        $msg = "$casinoKey different than $casinoKey2 for $homeTeam amount $lineAmmount vs $awayTeam" . "Spread Mismatch of $spreadMismatch Betting side $bettingSideName";
        if ($this->shouldSend()) {
            $this->sendTextMessage($msg, '+17203254863');
        }
    }

    public function shouldSend(): bool
    {
        return env('NOTIFICATIONS_ON');
    }

}
