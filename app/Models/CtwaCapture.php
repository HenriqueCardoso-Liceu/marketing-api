<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CtwaCapture extends Model
{
    protected $table = 'ctwa_capture';

    protected $fillable = [
        'phone',
        'phone_last8',
        'ctwa_clid',
        'ad_id',
        'source_type',
        'source_url',
        'headline',
        'body',
        'waba_id',
        'phone_number_id',
        'wamid',
        'msg_ts',
    ];

    protected $casts = [
        'msg_ts' => 'datetime',
    ];
}
