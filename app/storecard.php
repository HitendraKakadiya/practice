<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\DateNotification;
use Benwilkins\FCM;

class storecard extends Model
{
    public function routeNotificationFor($notification)
    {
        return $this->user->device_token;
    }

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
        'isActive',
        'is_Used',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
