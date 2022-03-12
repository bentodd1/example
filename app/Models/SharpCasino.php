<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharpCasino extends Model
{
    use HasFactory;
    protected $fillable = [
            'id',
            'casinoId',
            'apiKey'
        ];

}
