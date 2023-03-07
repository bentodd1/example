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
        'nonSharpBettingLineId',
        'end_date',
        'duration'
    ];

    public function originalLine()
    {
         return $this->hasOne(GameBettingLine::class, 'id','sharpBettingLineId');
    }

    public function movingLine()
    {
        return $this->hasOne(GameBettingLine::class, 'id', 'nonSharpBettingLineId');
    }

    public function getBettingSide(){

        $sharpLine = $this->originalLine()->first();
        $nonSharpLine = $this->movingLine()->first();
        $sharpHomeTeamSpread = $sharpLine['homeTeamSpread'];
        $nonSharpHomeTeamSpread = $nonSharpLine['homeTeamSpread'];

        if ($sharpHomeTeamSpread > $nonSharpHomeTeamSpread) {
            return 'awayTeam';
        }
        if ($sharpHomeTeamSpread < $nonSharpHomeTeamSpread) {
            return 'homeTeam';

        }
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($simulatedBet) {
            if ($simulatedBet->isDirty('end_date')) {
                $duration = $simulatedBet->end_date->diffInSeconds($simulatedBet->created_at);
                $simulatedBet->duration = $duration;
            }
        });
    }

}
