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
        'isCurrent',
        'created_at',
        'updated_at',
        'expired_time'
    ];

    public function casino()
    {
        return $this->hasOne(Casino::class, 'id','casinoId');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'gameId', 'id');
    }



}
