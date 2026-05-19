<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class leads extends Model
{
    protected $table = 'leads';    

    protected $fillable = [
        'name',
        'mobile_phone',
        'email',
        'city',
        'date_of_birth',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer',
        'landing_page',
    ];
}
