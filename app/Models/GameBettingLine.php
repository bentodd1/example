<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameBettingLine extends Model
{
    use HasFactory;
    protected $fillable = [
        'casinoId',
        'gameId',
        'homeTeamSpread',
        'awayTeamSpread',
    ];
}
