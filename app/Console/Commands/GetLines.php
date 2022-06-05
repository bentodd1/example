<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Models\Game;
use App\Models\GameBettingLine;
use App\Models\NonSharpCasino;
use App\Models\SharpCasino;
use App\Models\SimulatedBet;
use App\Models\Sport;
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
        //
        $this->findLineDiffs($newLines);
    }

    /**
     * @param GameBettingLine[] $lines
     * add isCurrent
     */
    public function findLineDiffs(array $lines)
    {
        // For now compare with a few casinos.
        $nonSharpCasinos = NonSharpCasino::all('casinoId')->toArray();
        $nonSharpCasinoIds =[];
        foreach($nonSharpCasinos as $nonSharpCasino){
            $nonSharpCasinoIds[] = $nonSharpCasino['casinoId'];
        }

        foreach ($lines as $line) {
            if(!in_array($line['casinoId'], $nonSharpCasinoIds))
            {
                continue;
            }

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

                    $simulatedBet = new SimulatedBet(['sharpBettingLineId' => $otherLine['id'], 'nonSharpBettingLineId' => $line['id']]);
                    $simulatedBet->save();
                    $bettingSide  = $simulatedBet->getBettingSide();
                    $bettingSideName = $homeTeam;
                    if($bettingSide == 'awayTeam') {
                        $bettingSideName = $awayTeam;
                    }
                    $lineAmmount = $simulatedBet->nonSharpLine()->first()['homeTeamSpread'];
                    $this->alert("$casinoKey different than $casinoKey2 for $homeTeam vs $awayTeam");
                    $this->alert("Game has a spread Mismatch of $homeDiff");
                    $msg = "$casinoKey different than $casinoKey2 for $homeTeam amount $lineAmmount vs $awayTeam" . "Spread Mismatch of $homeDiff Betting side $bettingSideName";
                    $SnSclient = new SnsClient([
                        'version' => '2010-03-31',
                        'region' => 'us-east-1',
                        'credentials' => new Credentials(
                            env('AWS_KEY'),
                            env('AWS_SECRET')

                        )
                    ]);

                    $message = $msg;
                    $phone = '+17203254863';

                    try {
                        $result = $SnSclient->publish([
                            'Message' => $message,
                            'PhoneNumber' => $phone,
                        ]);
                    } catch (AwsException $e) {
                        error_log($e->getMessage());
                    }

                }
            }

        }
    }
}
