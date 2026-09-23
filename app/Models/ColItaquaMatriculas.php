<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColItaquaMatriculas extends Model
{
    //
    protected $table = 'matriculas2027-col-itaqua';

    protected $fillable = [
        'responsible_name',
        'mobile_phone',
        'interest',
    ];
}
