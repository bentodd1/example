<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\NonSharpCasino;
use App\Models\SharpCasino;
use App\Models\SimulatedBet;
use App\Models\Sport;
use App\Services\GetLinesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Aws\Credentials\Credentials;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

class GetLines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetLines {key}';

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
        $apiKey = env('ODDS_API_KEY');
        $response = Http::accept('application/json')->get("https://api.the-odds-api.com/v4/sports/$sportType/odds/?apiKey=$apiKey&regions=us&markets=spreads&oddsFormat=american");
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
            $game = Game::where('sportId', $sport['id'])->where('homeTeam', $homeTeam)->where('awayTeam', $awayTeam)->where('apiKey', $apiGame['id'])
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
                $currentBetLine = GameBettingLine::where('gameId', $game['id'])->where('casinoId', $casino['id'])->where('isCurrent', true)->orderBy('updated_at', 'desc')->first();
                if (!$currentBetLine) {

                    $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread, 'isCurrent' => true]);
                    $gameBettingLine->save();
                    $newLines[] = $gameBettingLine;
                } else {
                    if (($currentBetLine['homeTeamSpread'] != $homeTeamSpread)) {

                        $gameBettingLine = new GameBettingLine(['gameId' => $game['id'], 'casinoId' => $casino['id'], 'homeTeamSpread' => $homeTeamSpread, 'awayTeamSpread' => $awayTeamSpread, 'isCurrent' => true]);
                        $gameBettingLine->save();
                        $currentBetLine['isCurrent'] = false;
                        $currentBetLine['expired_time'] = Carbon::now();
                        $currentBetLine->save();

                        $newLines[] = $gameBettingLine;
                    }
                }

            }
        }
        $this->alert('Number of new lines');

        $this->alert(sizeof($newLines));
        $scoreService = new GetLinesService();
        $scoreService->findLineDiffs($newLines);
    }


}
