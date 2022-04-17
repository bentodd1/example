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
        $response = Http::accept('application/json')->get("https://api.the-odds-api.com/v4/sports/$sportType/scores/?apiKey=36bd682a540e1d9e705584c352333111&completed=true");
        $games = $response->json();
        $scoreService = new ScoreService();

        foreach ($games as $apiGame) {
            $homeTeam = $apiGame['home_team'];
            $awayTeam = $apiGame['away_team'];
            $commenceTime = $apiGame['commence_time'];
            $commenceTime = str_replace('T', ' ', $commenceTime);
            $commenceTime = str_replace('Z', '', $commenceTime);
            $lastUpdated = $apiGame['last_update'];
            $lastUpdated = str_replace('T', ' ', $lastUpdated);
            $lastUpdated = str_replace('Z', '', $lastUpdated);

            $this->alert("Getting game $homeTeam vs $awayTeam");
            $game = Game::where('sportId', $sport['id'])->where('homeTeam', $homeTeam)->where('awayTeam', $awayTeam)->where('commenceTime', $commenceTime)
                ->first();
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
                $homeTeamSpread = $scoreService->calculateHomeTeamSpread($score);
                $simulatedBets = SimulatedBet::where('gameId', $game['id'])->get();

                foreach ($simulatedBets as $simulatedBet) {
                    $simulatedBet = $scoreService->changeWonStatus($simulatedBet, $homeTeamSpread);
                    $simulatedBet->save();
                }

            } else {
                $this->alert('Game does not exists!');
            }
        }

        return 0;
    }
}
