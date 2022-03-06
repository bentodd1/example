<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    // {"key":"americanfootball_nfl_super_bowl_winner",
    //"group":"American Football",
    //"title":"NFL Super Bowl Winner",
    //"description":"Super Bowl Winner 2022/2023",
    //"active":true,
    //"has_outrights":true
    use HasFactory;
    protected $fillable = [
        'key',
        'group',
        'title',
        'description',
        'active',
        'has_outrights'
    ];
}
