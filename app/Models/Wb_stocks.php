<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wb_stocks extends Model
{
    use HasFactory;
    protected $table = 'wb_stocks';
    protected $guarded = false;
    public $timestamps = false;
}
