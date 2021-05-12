<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class storecard extends Model
{
    protected $fillable = [
        'st_id',
        'user_id',
        'cardname',
        'carddetail',
        'cardno',
        'rewardpercen',
        'expdate',
        'card_img',
        'status',
    ];
}
