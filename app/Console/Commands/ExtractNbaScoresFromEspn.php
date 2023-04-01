<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Score;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExtractNbaScoresFromEspn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExtractNbaFromEspn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // Used for ncaab
        // Set the URL to scrape
        $this->loopThroughEveryDate();


        return 0;
    }

    public function loopThroughDays() {
        $start_date = Carbon::create(2020, 01, 7);
        $end_date = Carbon::today();

        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $formatted_date = $date->format('Ymd');

            // Do something with $formatted_date, such as save it to an array or use it in a query
        }
    }

    public function getUrl(string $date) {
        $url = "https://www.espn.com/nba/scoreboard/_/date/$date";
        return $url;
    }

    public function loopThroughEveryDate()
    {
        //$start_date = Carbon::create(2021, 01, 01);
        $start_date = Carbon::create(2021, 02, 17);
        $end_date = Carbon::today();

        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $formatted_date = $date->format('Ymd');
            $url = $this->getUrl($formatted_date);
            $this->getAllScores($url, $formatted_date);
            // Do something with $formatted_date, such as save it to an array or use it in a query
        }
    }

    public function getAllScores(string $url, string $date) {
        $html = file_get_contents($url);

// Load the HTML content into a DOM document
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

// Create an XPath object
        $xpath = new DOMXPath($dom);

// Define the XPath expression to get the games
        $expr = "//ul[contains(@class,'ScoreboardScoreCell__Competitors')]";

// Evaluate the XPath expression and get the nodes
        $nodes = $xpath->query($expr);

// Loop through the nodes and extract the scores
        foreach($nodes as $node) {
            $teamNames = [];
            $teamScores = [];


            $li_elements = $node->getElementsByTagName('li');
            $homeLi = $li_elements[1];
            $awayLi = $li_elements[0];
                // Get the team name
                $awayTeamName = $awayLi->getElementsByTagName('div')[0]
                    ->getElementsByTagName('div')[0]
                    ->textContent;

                $awayScoreElement = $awayLi->getElementsByTagName('div')[9];

            $homeTeamName = $homeLi->getElementsByTagName('div')[0]
                ->getElementsByTagName('div')[0]
                ->textContent;

            $homeScoreElement = $homeLi->getElementsByTagName('div')[9];
                if(!$awayScoreElement || !$homeScoreElement) {
                    continue;
                }
                $homeTeamScore = $homeScoreElement->textContent;
                $awayTeamScore = $awayScoreElement->textContent;
                // Get the score


            $this->findGameAndCreateScore($homeTeamName, $awayTeamName, $homeTeamScore, $awayTeamScore,$date);

        }
    }

    public function findGameAndCreateScore(string $homeTeamName, string $awayTeamName, string $homeTeamScore, string $awayTeamScore, string $date) {
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $formattedDate = date('Y-m-d', strtotime("$year-$month-$day"));
        $query = DB::table('games');

        $query->where('homeTeam', 'LIKE', '%'.$homeTeamName.'%');

        $query->where('awayTeam', 'LIKE', '%'. $awayTeamName.'%');
        $game = $query->whereDate('commenceTime', $formattedDate)->first();
        if($game && $homeTeamName && $homeTeamScore){
            echo 'Match found';
            $now = now();
            $score = new Score(['homeTeamScore' => $homeTeamScore,'awayTeamScore' => $awayTeamScore, 'gameId' => $game->id, 'sportId' =>$game->sportId, 'apiId' =>$game->apiKey, 'lastUpdated' => $now ]);
            $score->save();
            echo 'Match made';
        }
    }
}
