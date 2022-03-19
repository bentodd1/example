<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\SharpCasino;
use App\Models\SimulatedBet;
use App\Models\Sport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


//Make the option to do all sports or a single sports
// Don't crash when it can't find certain things.
// Make job that checks all new lines
// Make a cron that runs every minute
// Send an email or text to verify

// DOES NOT WORK when game has alreadtstarted
class GetLines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetLines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get those lines';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *  "bookmakers": [
     * {
     * "key": "draftkings",
     * "title": "DraftKings",
     * "last_update": "2022-03-07T01:22:59Z",
     * "markets": [
     * {
     * "key": "spreads",
     * "outcomes": [
     * {
     * "name": "Furman Paladins",
     * "price": 100,
     * "point": -2.5
     * },
     * {
     * "name": "Samford Bulldogs",
     * "price": -130,
     * "point": 2.5
     * }
     * ]
     * }
     * ]
     *
     * @return int
     */
    public function handle()
    {
        $sport = Sport::where('key', 'basketball_ncaab')->first();
        $response = Http::accept('application/json')->get('https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds/?apiKey=1a12221aff5a1654bb760995fdfea015&regions=us&markets=spreads&oddsFormat=american');
        $allGames = $response->json();
        $newLines = [];

        foreach ($allGames as $apiGame) {
            $homeTeam = $apiGame['home_team'];
            $awayTeam = $apiGame['away_team'];
            $commenceTime = $apiGame['commence_time'];
            $commenceTime = str_replace('T', ' ', $commenceTime);
            $commenceTime = str_replace('Z', '', $commenceTime);
            if($commenceTime < Carbon::now('UTC'))
            {
                continue;
            }
            $this->alert("Getting game $homeTeam vs $awayTeam");
            $game = Game::where('sportId', $sport['id'])->where('homeTeam', $homeTeam)->where('awayTeam', $awayTeam)->where('commenceTime', $commenceTime)
                ->first();
            if (!$game) {
                $game = new Game(['sportId' => $sport['id'], 'apiKey' => $apiGame['id'], 'homeTeam' => $homeTeam, 'awayTeam' => $awayTeam, 'commenceTime' => $commenceTime]);
                $game->save();
            }

            $bookMakers = $apiGame['bookmakers'];

            foreach ($bookMakers as $bookMaker) {
                $key = $bookMaker['key'];
                $casino = Casino::where('key', $key)->first();
                if (!$casino) {
                    $casino = new Casino(['key' => $bookMaker['key'], 'title' => $bookMaker['title']]);
                    $casino->save();
                }
                $markets = $bookMaker['markets'];
                $homeTeamSpread = 0;
                $awayTeamSpread = 0;
                foreach ($markets as $market) {
                    if ($market['key'] == 'spreads') {
                        $outcomes = $market['outcomes'];
                        foreach ($outcomes as $outcome) {

                            if ($outcome['name'] == $homeTeam) {
                                $homeTeamSpread = $outcome['point'];
                            }
                            if ($outcome['name'] == $awayTeam) {
                                $awayTeamSpread = $outcome['point'];
                            }
                        }
                    }
                }
                $currentBetLine = GameBettingLine::where('gameId', $game['id'])->where('casinoId', $casino['id'])->where('homeTeamSpread', $homeTeamSpread)->where('awayTeamSpread', $awayTeamSpread)->where('isCurrent', true)->orderBy('updated_at', 'desc')->first();
                if (!$currentBetLine) {

                    $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread, 'isCurrent' => true]);
                    $gameBettingLine->save();
                    $newLines[] = $gameBettingLine;
                } else {
                    if (($currentBetLine['homeTeamSpread'] != $homeTeamSpread)) {

                        $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread, 'isCurrent' => true]);
                        $gameBettingLine->save();
                        $currentBetLine['isCurrent'] = false;
                        $currentBetLine->save();
                        $newLines[] = $gameBettingLine;
                    }
                }

            }
        }
        $this->alert('Number of new lines');

        $this->alert(sizeof($newLines));
        $this->findLineDifs($newLines);
    }

    /**
     * @param GameBettingLine[] $lines
     * add isCurrent
     */
    public function findLineDifs(array $lines)
    {
        // For now compare with a few casinos.

        foreach ($lines as $line) {
            $homeSpread = $line['homeTeamSpread'];
            $awaySpread = $line['awayTeamSpread'];
            $gameId = $line['gameId'];
            $sharpCasinoIds = SharpCasino::all('casinoId');
            $otherLines = GameBettingLine::where('gameId', $gameId)->where('isCurrent', true)->whereIn('casinoId', $sharpCasinoIds)->get();
            foreach ($otherLines as $otherLine) {
                $homeCompareSpread = $otherLine['homeTeamSpread'];
                $awayCompareSpread = $otherLine['awayTeamSpread'];
                $homeDiff = $homeSpread - $homeCompareSpread;
                $awayDiff = $awaySpread - $awayCompareSpread;

                if (abs($homeDiff) > 1.4 || abs($awayDiff) > 1.4) {
                    $game = Game::where('id', $line['gameId'])->first();
                    $homeTeam = $game['homeTeam'];
                    $awayTeam = $game['awayTeam'];
                    $casino = Casino::where('id', $line['casinoId'])->first();
                    $casinoKey = $casino['key'];
                    $casino2 = Casino::where('id', $otherLine['casinoId'])->first();
                    $casinoKey2 = $casino2['key'];
                    $this->alert("$casinoKey different than $casinoKey2 for $homeTeam vs $awayTeam");
                    $this->alert("Game has a spread Mismatch of $homeDiff");
                    $msg = "$casinoKey different than $casinoKey2 for $homeTeam vs $awayTeam" . "Game has a spread Mismatch of $homeDiff";
// send email
                    mail("someone@example.com","Spread Mismatch",$msg);
                    $simulatedBet = new SimulatedBet(['sharpBettingLineId' => $otherLine['id'], 'nonSharpBettingLineId' => $line['id']]);
                    $simulatedBet->save();

                }
            }

        }
    }
}
