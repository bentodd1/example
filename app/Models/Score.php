<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    protected $fillable = [
        'gameId',
        'sportId',
        'homeTeamScore',
        'awayTeamScore',
        'apiId',
        'lastUpdated',
        'source'
    ];
}

