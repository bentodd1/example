<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Score;
use DOMDocument;
use DOMXPath;

class HistoricalScoreService
{
    /**
     *         'id',
    'sportId',
    'apiKey',
    'commenceTime',
    'homeTeam',
    'awayTeam'
     */
    public static function extractScoreFromGame(Game $game): ?Score
    {
        $commenceTime = new \DateTime($game['commenceTime']);
        $sport = $game->sport()->first();
        $key = $sport->key;
        $url = self::createUrlForEspn($commenceTime, $key);
        $allScores = self::getAllScores($url);
        $lastUpdated = new \DateTime();
        $score = self::matchScoreToGame($game, $allScores);
        $score1 = null;
        if(empty($score))
        {
            $commenceTime = $commenceTime->modify('-1 day');
            $sport = $game->sport()->first();
            $key = $sport->key;
            $url = self::createUrlForEspn($commenceTime, $key);
            $allScores = self::getAllScores($url);
            $score = self::matchScoreToGame($game, $allScores);
        }
        if(!empty($score)) {
            $score1 = new Score(['gameId' => $game['id'],
                'sportId' => $sport['id'],
                'homeTeamScore' => $score['homeTeamScore'],
                'awayTeamScore' => $score['awayTeamScore'],
                'apiId' => $game['id'],
                'lastUpdated' => $lastUpdated, 'source' => $url]);
            $score1->save();

        }
        else
        {
            $homeTeam = $game['homeTeam'];
            $awayTeam = $game['awayTeam'];
            $myCommence = $game['commenceTime'];
            echo "Cannot find $awayTeam at $homeTeam during $myCommence";
        }
        return $score1;

    }

    public static function createUrlForEspn(\DateTime $commenceTime, $sport): string {
        $sport = self::extractEspnSportFromOddsSport($sport);
        $espn_date_string = $commenceTime->format('Ymd');
        $url = "https://www.espn.com/$sport/scoreboard/_/date/$espn_date_string/seasontype/2/group/50";
        return $url;
    }

    public static function extractEspnSportFromOddsSport(string $sport): string {
        if($sport == 'basketball_ncaab')
        {
            $sport = 'mens-college-basketball';
        }
        if($sport == 'basketball_nba'){
            $sport = 'nba';
        }
        return $sport;
    }

    public static function getAllScores(string $url): array
    {
        echo $url;
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

        $scoreHashes = [];
// Loop through the nodes and extract the scores
        foreach ($nodes as $node) {

            $li_elements = $node->getElementsByTagName('li');
            $homeLi = $li_elements[1];
            $awayLi = $li_elements[0];

            // NBA is 9 ncaa is 7
            $awayScoreElement = $awayLi->getElementsByTagName('div')[9];
            $homeScoreElement = $homeLi->getElementsByTagName('div')[9];

            $aTag  = $awayLi->getElementsByTagName('a');
            if(!$aTag[0]){
                continue;
            }
            $href = $aTag[0]->getAttribute('href');
            $awayName = str_replace('-', ' ', substr($href, strrpos($href, '/') + 1));
            $awayTeamName = ucwords($awayName);
            // Get the team name

            if(!$homeLi->getElementsByTagName('a')[0]){
                continue;
            }
            $href = $homeLi->getElementsByTagName('a')[0]->getAttribute('href');
            $homeName = str_replace('-', ' ', substr($href, strrpos($href, '/') + 1));
            $homeTeamName = ucwords($homeName);

            //TODO score elements not being found
            if (!$awayScoreElement || !$homeScoreElement) {
                continue;
            }
            $homeTeamScore = $homeScoreElement->textContent;
            $awayTeamScore = $awayScoreElement->textContent;

            $now = new \DateTime();

            $scoreHash = ['homeTeamName' => $homeTeamName, 'awayTeamName' => $awayTeamName, 'homeTeamScore' => $homeTeamScore, 'awayTeamScore' => $awayTeamScore, 'lastUpdated' => $now];
            // Get the score
            $scoreHashes[] = $scoreHash;
        }
        return $scoreHashes;
    }

    // TODO This method needs to be tested with the data that didn't work
    public static function matchScoreToGame(Game $game, array $allScores) :array
    {
        $homeTeamName = $game['homeTeam'];
        foreach ($allScores as $score)
        {
            if($score['homeTeamName'] == $homeTeamName)
            {
                return $score;
            }
        }
        return [];
    }




}
