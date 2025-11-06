<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    use KeyGenerate;
    protected $table = 'mutasi';
    protected $fillable = [
        'location_id',
        'type',
        'user_id', 
        'code', 
        'description'
    ];
}
