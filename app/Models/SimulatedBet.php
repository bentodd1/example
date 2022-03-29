<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulatedBet extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
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
}
