<?php

namespace App\Console\Commands;

use App\Models\Sport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;


class HitApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetSports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some description';

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
     *        'key',
    'group',
    'title',
    'description',
    'active',
    'has_outrights'
     *
     * @return int
     */
    public function handle()
    {
        //$name = $this->ask('What is your name?');
        $response = Http::accept('application/json')->get('https://api.the-odds-api.com/v4/sports?apiKey=1a12221aff5a1654bb760995fdfea015');
        $allSports = $response->json();
        foreach ($allSports as $sport){
            $sport  = new Sport(['key'=>$sport['key'], 'group'=>$sport['group'], 'description'=>$sport['description'],'active'=>$sport['active'], 'has_outrights'=>$sport['has_outrights']]);
            $sport->save();
        }

    }
}
