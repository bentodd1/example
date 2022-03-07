<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{

    use HasFactory;
    protected $fillable = [
        'id',
        'key',
        'group',
        'title',
        'description',
        'active',
        'has_outrights'
    ];
}
