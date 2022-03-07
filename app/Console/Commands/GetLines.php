<?php

namespace App\Console\Commands;

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

        $response = Http::accept('application/json')->get('https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds/?apiKey=1a12221aff5a1654bb760995fdfea015&regions=us&markets=spreads&oddsFormat=american
');

        $allGames = $response->json();

        //TODO needs to match commenceTime
        foreach ($allGames as $apiGame){
            $homeTeam = $apiGame['homeTeam'];
            $awayTeam = $apiGame['awayTeam'];
            $game = Game::where('sportId', $sport['id'])->where('homeTeam', $homeTeam)->where('awayTeam', $awayTeam)
                ->first();
            if(!$game){
                $game = new Game();
            }

            $bookMakers = $game['bookmakers'];

            foreach ($bookMakers as $bookMaker){
                $bookMaker = $bookMaker['key'];
                $markets = $bookMaker['markets'];
                foreach ($markets as $market){
                    if($market['key'] == 'spreads')
                    {
                        $outcomes = $market['outcomes'];

                    }
                }


            }
        }

            //TODO get most recent.
            $line = GameBettingLine::where('sportId', $sport['id'])->where('homeTeam', $line['homeTeam'])->where('awayTeam', $line['awayTem'])
                ->get();
            if(!$line){
                $line = new GameBettingLine();
                $line->save();
            }
            else{
                //Check if line is different.
                // If so Create a new one.
                // Record the line Movement.
            }


    }
}
