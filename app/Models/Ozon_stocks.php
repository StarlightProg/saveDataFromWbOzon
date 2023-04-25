<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ozon_stocks extends Model
{
    use HasFactory;

    protected $table = 'oz_info_stocks';
    protected $guarded = false;
    public $timestamps = false;
}
