<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    /** @use HasFactory<\Database\Factories\ProductsFactory> */
    protected $fillable = [
        "product_id",
        "name",
        "description",
        "price",
        "isTrash",
        "types_id"
    ];
    use HasFactory;

    public function orderItems()
    {
        return $this->hasMany(Orderitems::class);
    }

    public function types()
    {
        return $this->belongsTo(Types::class, 'types_id');
    }
}
