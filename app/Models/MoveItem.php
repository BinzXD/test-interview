<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Model;

class MoveItem extends Model
{
    use KeyGenerate;
    protected $table = 'move_items';
    protected $fillable = [
        'move_id',
        'product_id',
        'qty',
    ];
}
