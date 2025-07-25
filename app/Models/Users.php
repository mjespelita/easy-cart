<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    /** @use HasFactory<\Database\Factories\UsersFactory> */
protected $fillable = ["name","email","password","role","isTrash"];
    use HasFactory;
}
