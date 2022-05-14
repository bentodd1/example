<?php

namespace App\Console\Commands;

use App\Models\SimulatedBet;
use Illuminate\Console\Command;
use App\Services\ScoreService;

class RetroMatchScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RetroMatchScores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retro Matching Scores';

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
        $simulatedBets = SimulatedBet::where(['won' => null])->get();
       // $simulatedBets = SimulatedBet::all();
        $scoreService = new ScoreService();
        foreach ($simulatedBets as $simulatedBet) {
            $simulatedBet = $scoreService->retroMatchBet($simulatedBet);
            $simulatedBet->save();
        }
        return 1;
    }
}
