<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use App\Helpers\UploadHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use KeyGenerate, SoftDeletes;
    protected $table = 'products';
    protected $fillable = [
        'name',
        'code',
        'image',
        'unit',
        'category_id',
        'price',
    ];

    public function getImageAttribute($value)
    {
        if ($value) {
            $image = UploadHelper::getFileUrl($value);
            return $image ?: null;
        }

        return null;
    }

      protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}
