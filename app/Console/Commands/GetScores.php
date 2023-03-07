<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Score;
use App\Models\SimulatedBet;
use App\Models\Sport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\ScoreService;

class GetScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetScores{key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Those Scores';

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
        $response = Http::accept('application/json')->get("https://api.the-odds-api.com/v4/sports/$sportType/scores/?apiKey=0e799a93e51b4c76d5ca762a7054aa00&daysFrom=2");
        $games = $response->json();

        //$scoreService->

        foreach ($games as $apiGame) {
            if ($apiGame['completed']) {
                $homeTeam = $apiGame['home_team'];
                $awayTeam = $apiGame['away_team'];
                $lastUpdated = $apiGame['last_update'];
                $lastUpdated = str_replace('T', ' ', $lastUpdated);
                $lastUpdated = str_replace('Z', '', $lastUpdated);

                $this->alert("Getting game $homeTeam vs $awayTeam");
                $game = Game::where('apiKey', $apiGame['id'])
                    ->first();
                if (!$game) {
                    $commenceTime = $apiGame['commence_time'];
                    $commenceTime = str_replace('T', ' ', $commenceTime);
                    $commenceTime = str_replace('Z', '', $commenceTime);
                    $game = new Game(['sportId' => $sport['id'], 'apiKey' => $apiGame['id'], 'homeTeam' => $homeTeam, 'awayTeam' => $awayTeam, 'commenceTime' => $commenceTime]);
                    $game->save();
                }
                if ($game) {
                    $score = Score::where('gameId', $game['id'])->first();
                    if ($score) {
                        continue;
                    }
                    $awayTeamScore = 0;
                    $homeTeamScore = 0;
                    $scores = $apiGame['scores'];
                    if ($scores) {
                        foreach ($scores as $score) {
                            if ($score['name'] == $homeTeam) {
                                $homeTeamScore = $score['score'];
                            }
                            if ($score['name'] == $awayTeam) {
                                $awayTeamScore = $score['score'];
                            }

                        }
                    }
                    $this->alert('Game exists!');
                    $score = new Score(['gameId' => $game['id'],
                        'sportId' => $sport['id'],
                        'homeTeamScore' => $homeTeamScore,
                        'awayTeamScore' => $awayTeamScore,
                        'lastUpdated' => $lastUpdated,
                        'apiId' => $apiGame['id']]);
                    $score->save();

                }
            }
        }
        return 0;
    }
}
