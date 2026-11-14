<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function subcategory()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function rawMaterialProducts()
    {
        return $this->hasMany(Product::class, 'category_id')
            ->where(function ($q) {
                $q->where('is_raw_material', 1)
                  ->orWhere('is_part', 1);
            });
    }

    public function scopeRawMaterials($query)
    {
        return $query->where('is_raw_material', 1);
    }

    public function scopeFinishedGoods($query)
    {
        return $query->where('is_raw_material', 0);
    }
}
