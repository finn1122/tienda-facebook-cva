<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;


    protected $table = 'brands';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name', 'image_link', 'active'
    ];
}
