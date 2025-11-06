<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Move extends Model
{
    use KeyGenerate, SoftDeletes;
    protected $table = 'move';
    protected $fillable = [
        'source_location_id',
        'destination_location_id',
        'user_id',
        'code',
        'description',
    ];
}
