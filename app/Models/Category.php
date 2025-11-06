<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use KeyGenerate, SoftDeletes;

    protected $table = 'category';
    protected $fillable = [
        'name',
        'status',
        'code'
    ];
}
