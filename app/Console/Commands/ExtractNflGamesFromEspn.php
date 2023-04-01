<?php

namespace App\Console\Commands;

use App\Models\Score;
use Carbon\Carbon;
use DateTime;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExtractNflGamesFromEspn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExtractNflFromEspn';

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
// Set the range of seasons and weeks to scrape
        $seasons = range(2023, 2019, -1);
        $weeks = range(1, 17);

// Create an empty array to store the scraped data
        $scores_data = [];

// Loop through each season and week and scrape the scores data
        foreach ($seasons as $season) {
            foreach ($weeks as $week) {
                // Create the URL for the scores page
                $url = "https://www.espn.com/nfl/scoreboard/_/year/$season/seasontype/2/week/$week";
                // Load the HTML content of the scores page
                $this->getAllScores($url, '');
                // Store the data in an associative array and append it to the scores_data array
            }
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

        $dateExpr = "//h3[contains(@class, 'Card__Header__Title' )]";
        $dates = $xpath->query($dateExpr);
        $formattedDates = [];
        foreach ($dates as $date){
            $textContent  = $date->textContent;
            echo $textContent . "\n";
            $dateF = DateTime::createFromFormat('D, F j, Y', $textContent);
            if($dateF) {
                $formattedDates[] = $dateF->format('Y-m-d');
            }
        }


// Loop through the nodes and extract the scores
        foreach($nodes as $node) {

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

            echo $awayTeamName ;
            echo $homeTeamName;

         $this->findGameAndCreateScore($homeTeamName, $awayTeamName, $homeTeamScore, $awayTeamScore, $formattedDates);

        }
    }

    public function findGameAndCreateScore(string $homeTeamName, string $awayTeamName, string $homeTeamScore, string $awayTeamScore, array $formattedDates) {
        $firstDate = reset($formattedDates);
        $lastDate = end($formattedDates);
        $firstAndLast = [$firstDate, $lastDate];
        $query = DB::table('games');
        $query->where('homeTeam', 'LIKE', '%'.$homeTeamName.'%')->where('awayTeam', 'LIKE', '%'. $awayTeamName.'%')
        ->whereBetween('commenceTime', $firstAndLast);
        $game = $query->first();

        if($game && $homeTeamName && $homeTeamScore){
            echo 'Match found';
            $now = now();
            $score = new Score(['homeTeamScore' => $homeTeamScore,'awayTeamScore' => $awayTeamScore, 'gameId' => $game->id, 'sportId' =>$game->sportId, 'apiId' =>$game->apiKey, 'lastUpdated' => $now ]);
            $score->save();
            echo 'Match made';
        }
    }
}
