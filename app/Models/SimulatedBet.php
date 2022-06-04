<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulatedBet extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'won',
        'sharpBettingLineId',
        'nonSharpBettingLineId'
    ];

    public function sharpLine()
    {
         return $this->hasOne(GameBettingLine::class, 'id','sharpBettingLineId');
    }

    public function nonSharpLine()
    {
        return $this->hasOne(GameBettingLine::class, 'id', 'nonSharpBettingLineId');
    }

    public function getBettingSide(){

        $sharpLine = $this->sharpLine()->first();
        $nonSharpLine = $this->nonSharpLine()->first();
        $sharpHomeTeamSpread = $sharpLine['homeTeamSpread'];
        $nonSharpHomeTeamSpread = $nonSharpLine['homeTeamSpread'];

        if ($sharpHomeTeamSpread > $nonSharpHomeTeamSpread) {
            return 'awayTeam';
        }
        if ($sharpHomeTeamSpread < $nonSharpHomeTeamSpread) {
            return 'homeTeam';

        }
    }

}
