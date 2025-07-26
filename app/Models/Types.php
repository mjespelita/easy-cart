<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Types extends Model
{
    /** @use HasFactory<\Database\Factories\TypesFactory> */
    protected $fillable = ["name","isTrash"];
    use HasFactory;

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }
}
