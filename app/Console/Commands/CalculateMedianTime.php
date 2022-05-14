<?php

namespace App\Console\Commands;

use App\Models\GameBettingLine;
use Illuminate\Console\Command;

class CalculateMedianTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CalculateMedianTime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the median time';

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
        $this->alert('In here');

        $lines = GameBettingLine::whereNotNull('expired_time')->get();
        $times = [];
        foreach ($lines as $line) {
            $this->alert('In here');
            $createdAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $line->created_at);
            $expiredAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $line->expired_time);
            $differenceInMinutes = $createdAt->diffInMinutes($expiredAt);
            $times[] = $differenceInMinutes;
        }

        sort($times);
        $count = sizeof($times);   // cache the count
        $index = floor($count / 2);  // cache the index
        if (!$count) {
            $this->alert("no values");
        } elseif ($count & 1) {    // count is odd
            $this->alert($times[$index]);
        } else {                   // count is even
            $this->alert(($times[$index - 1] + $times[$index]) / 2);
        }

        return 0;
    }
}
