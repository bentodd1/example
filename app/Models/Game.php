<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'sportId',
        'apiKey',
        'commenceTime',
        'homeTeam',
        'awayTeam'
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'sportId', 'id');
    }
}
