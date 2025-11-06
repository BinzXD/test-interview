<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Model;

class MutasiItem extends Model
{
    use KeyGenerate;
    protected $table = 'mutasi_items';
    protected $fillable = [
        'mutasi_id',
        'product_id',
        'qty',
    ];
}
