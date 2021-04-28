<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class storedata extends Model
{
    protected $fillable = [
        'user_id',
        'stname',
        'stlocation',
        'stcontact',
        'store_img',
    ];
}
