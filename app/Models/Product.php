<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name', 'description', 'warranty', 'inventory', 'cva_price', 'cva_currency'
        , 'price', 'sale_price', 'currency','cva_key', 'sku',  'image_link', 'active', 'brand_id', 'group_id'
    ];
}
