<?php

namespace App\Console\Commands;

use App\Models\Score;
use App\Services\HistoricalLinesService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CreateHistoricalePinnacleLines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreateHistoricalPinnacleLines';

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
        $api_key = "53497fac29b2ac2dfb9a14f82b6307a6";


        $current_date = Carbon::create(2021, 01, 01, 7, 0, 0, 'UTC');
        //$current_date = Carbon::create(2023, 02, 9, 7, 0, 0, 'UTC');

        while ($current_date->diffInDays(Carbon::now(), false) > 0)  {

            $current_date_string = $current_date->toIso8601ZuluString();
            //$pinnacle_url = "https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds-history/?apiKey=$api_key&regions=eu&bookmakers=pinnacle&markets=h2h,spreads&oddsFormat=american&date=$current_date_string";
            $pinnacle_url = "https://api.the-odds-api.com/v4/sports/basketball_nba/odds-history/?apiKey=$api_key&regions=eu&bookmakers=pinnacle&markets=h2h,spreads&oddsFormat=american&date=$current_date_string";

            $pinnacle_response = Http::get($pinnacle_url);
            $pinnacle_odds = $pinnacle_response->json();
           // $draftkings_url = "https://api.the-odds-api.com/v4/sports/basketball_ncaab/odds-history/?apiKey=$api_key&regions=us&bookmakers=draftkings&markets=h2h,spreads&oddsFormat=american&date=$current_date_string";
            $draftkings_url = "https://api.the-odds-api.com/v4/sports/basketball_nba/odds-history/?apiKey=$api_key&regions=us&bookmakers=draftkings&markets=h2h,spreads&oddsFormat=american&date=$current_date_string";

            $draftkings_response = Http::get($draftkings_url);
            $draftkings_odds = $draftkings_response->json();
            $keyedDraftKings = HistoricalLinesService::keyGamesById($draftkings_odds['data']);
            $keyedPinnacle = HistoricalLinesService::keyGamesById($pinnacle_odds['data']);
            echo 'current Date string ';
            echo $current_date_string;
            foreach($keyedPinnacle as $key => $game){
                if(isset($keyedDraftKings[$key]))
                {
                    HistoricalLinesService::compareTwoLines($keyedPinnacle[$key],$keyedDraftKings[$key], $pinnacle_url, $draftkings_url );
                }

            }
            $current_date->addDay();
            echo "In loop";
            echo "\n";
        }
        return 0;
    }



}
