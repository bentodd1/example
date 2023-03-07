<?php

namespace App\Console\Commands;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\Sport;
use App\Services\GetLinesService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AddHistoricalLines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddHistoricalLines{key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds Historical Lines';

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
     *
     * @return int
     */
    public function handle()
    {

        $sportType = $this->argument('key');

        $sport = Sport::where('key', $sportType)->first();
        if(!$sport)
        {
            $sport = new Sport(['key'=>$sportType ,'group'=>'Basketball' ,'description' => 'US College Basketball','active' =>1,'has_outrights'=>0] );
            $sport->save();
        }

        // Start Date         June 6th 2020

        $date = Carbon::create(2020, 10, 18, 7, 0, 0, 'UTC');
        while ($date->diffInDays(Carbon::now(), false) > 0) {
              $dateString = $date->toIso8601ZuluString();
              $response = $this->fetchResponseFromHistory($sportType, $dateString);
              $this->createLinesForGivenTime($response, $sport);
              $date->addDay(); // Move to the next day
        }


        return 0;
    }

    public function fetchResponseFromHistory(string $sport, $date ){
        $fetchString = "https://api.the-odds-api.com/v4/sports/$sport/odds-history/?apiKey=a1afa0961f51b8fef9289253c9dfb21f&regions=us&markets=spreads&oddsFormat=american&date=$date";
        $response = Http::accept('application/json')->get("https://api.the-odds-api.com/v4/sports/$sport/odds-history/?apiKey=a1afa0961f51b8fef9289253c9dfb21f&regions=us&markets=spreads&oddsFormat=american&date=$date");
        return $response;
    }

    public function createLinesForGivenTime($response, $sport) {
        $allGames = $response->json();
        $allGames = $allGames['data'];
        $newLines = [];

        foreach ($allGames as $apiGame) {
            print('in game');
            $homeTeam = $apiGame['home_team'];
            $awayTeam = $apiGame['away_team'];
            $commenceTime = $apiGame['commence_time'];
            $commenceTime = str_replace('T', ' ', $commenceTime);
            $commenceTime = str_replace('Z', '', $commenceTime);
            $this->alert("Getting game $homeTeam vs $awayTeam");
            $game = Game::where('sportId', $sport['id'])->where('homeTeam', $homeTeam)->where('awayTeam', $awayTeam)->where('apiKey', $apiGame['id'])
                ->first();
            if (!$game) {
                $game = new Game(['sportId' => $sport['id'], 'apiKey' => $apiGame['id'], 'homeTeam' => $homeTeam, 'awayTeam' => $awayTeam, 'commenceTime' => $commenceTime]);
                $game->save();
            }

            $bookMakers = $apiGame['bookmakers'];
            foreach ($bookMakers as $bookMaker) {
                $skipBookmaker = false;
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
                            if (!array_key_exists("point", $outcome)) {
                                $skipBookmaker = true;
                                break;
                            }
                            if(abs($outcome['price']) > 125 ) {
                                $skipBookmaker = true;
                            }

                            if ($outcome['name'] == $homeTeam) {
                                $homeTeamSpread = $outcome['point'];
                            }
                            if ($outcome['name'] == $awayTeam) {
                                $awayTeamSpread = $outcome['point'];
                            }

                        }
                    }
                }
                if(!$skipBookmaker) {
                        $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread, 'isCurrent' => true]);
                        $gameBettingLine->save();
                        $newLines[] = $gameBettingLine;
                    }
                }
        }
        $this->alert('Number of new lines');

        $this->alert(sizeof($newLines));
        $scoreService = new GetLinesService();
        $scoreService->findHistoricalLineDiffs($newLines);

    }

}
