<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\Sport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

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
    {
    "key": "draftkings",
    "title": "DraftKings",
    "last_update": "2022-03-07T01:22:59Z",
    "markets": [
    {
    "key": "spreads",
    "outcomes": [
    {
    "name": "Furman Paladins",
    "price": 100,
    "point": -2.5
    },
    {
    "name": "Samford Bulldogs",
    "price": -130,
    "point": 2.5
    }
    ]
    }
    ]
     *
     * @return int
     */
    public function handle()
    {
        $sport = Sport::where( 'key', 'basketball_ncaab')->first();

        $response = Http::accept('application/json')->get('https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds/?apiKey=1a12221aff5a1654bb760995fdfea015&regions=us&markets=spreads&oddsFormat=american');

        $allGames = $response->json();

        //TODO needs to match commenceTime
        foreach ($allGames as $apiGame){
            $homeTeam = $apiGame['home_team'];
            $awayTeam = $apiGame['away_team'];
            $game = Game::where('sportId', $sport['id'])->where('homeTeam', $homeTeam)->where('awayTeam', $awayTeam)
                ->first();
            if(!$game){
                //TODO fill this out
                $game = new Game();
            }

            $bookMakers = $apiGame['bookmakers'];

            foreach ($bookMakers as $bookMaker){
                $bookMaker = $bookMaker['key'];
                $casino = Casino::where()->first('key', $bookMaker);
                $markets = $bookMaker['markets'];
                $homeTeamSpread = 0;
                $awayTeamSpread= 0;
                foreach ($markets as $market){
                    if($market['key'] == 'spreads')
                    {
                        $outcomes = $market['outcomes'];
                        foreach ($outcomes as $outcome) {

                            //TODO GET OLD LINE
                            //TODO get most recent.
                            //Check if line is different.
                            // If so Create a new one.
                            // Record the line Movement.
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
                $gameBettingLine = new GameBettingLine(['gameId' => $game['id'],'casinoId' => $casino['id'],'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread]);
                $gameBettingLine->save();

            }
        }




    }
}
