<?php

namespace App\Console\Commands;

use App\Models\SimulatedBet;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetBettingStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetBettingStats';

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
        $simulatedBets = SimulatedBet::whereNotNull('won')->get();


        $activeCount = 0;
        $nonActiveCount = 0;
        $minutesActive = 0;
        $activeMinutes = [];

        foreach ($simulatedBets as $simulatedBet){
           $this->alert("Here");
            $nonSharpLine = $simulatedBet->nonSharpLine()->first();
            if($nonSharpLine['isActive'])
            {

                $activeCount++;
            }
            else {

                $timeActive = Carbon::parse($nonSharpLine['created_at'])->diffInMinutes(Carbon::parse($nonSharpLine['updated_at']->getTimestamp()));
                $activeMinutes[] = $timeActive;
                $this->alert("time $timeActive");

                $minutesActive += $timeActive;
                  $nonActiveCount++;
                }
        }
        $averageTime = $minutesActive/$nonActiveCount;
        $middleInt  = round($nonActiveCount/2);
        $median = $activeMinutes[$middleInt];
        $this->alert("Total active $activeCount");
        $this->alert("Total inActive $nonActiveCount");

        $this->alert("Average time $averageTime");
        $this->alert("Median time $median");



    }
}
