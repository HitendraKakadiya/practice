<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardShare extends Model
{
    protected $fillable = ['user_id', 'share_code', 'card_id', 'exptime'];

    protected $table = 'card_shares';
}
