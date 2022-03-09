<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\Sport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

//Make the option to do all sports or a single sports
// Don't crash when it can't find certain things.
// Make job that checks all new lines
// Make a cron that runs every minute
// Send an email or text to verify

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
                if(!$casino){
                    $casino = new Casino(['key' => $bookMaker['key'], 'title' => $bookMaker['title'] ]);
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
//
                            }
                            if ($outcome['name'] == $awayTeam) {
                                $awayTeamSpread = $outcome['point'];

                            }
                        }


                    }
                }
                $currentBetLine = GameBettingLine::where('gameId', $game['id'])->where('casinoId', $casino['id'])->where('homeTeamSpread', $homeTeamSpread)->where('awayTeamSpread', $awayTeamSpread)->orderBy('updated_at', 'desc')->first();
                if (!$currentBetLine)  {

                        $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread]);
                        $gameBettingLine->save();
                        $newLines[] = $gameBettingLine;
                }
                else {
                    if(($currentBetLine['homeTeamSpread'] != $homeTeamSpread)) {
                        $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread]);
                        $gameBettingLine->save();
                        $newLines[] = $gameBettingLine;
                    }
                }

            }
        }
        $this->alert(sizeof($newLines));
        foreach ($newLines as $newLine){

        }

    }
}
