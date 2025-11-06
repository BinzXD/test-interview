<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\KeyGenerate;

class StockLocation extends Model
{
    use KeyGenerate;
    protected $table = 'stock_product_location';
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['product_id', 'location_id', 'qty'];
}
