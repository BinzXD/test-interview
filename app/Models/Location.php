<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use KeyGenerate, SoftDeletes;
    
    protected $table = 'locations';
    protected $fillable = [
        'name',
        'address',
        'code'
    ];

}
