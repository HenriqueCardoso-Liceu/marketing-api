<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdsInsight extends Model
{
    protected $table = 'meta_ads_insights';

    protected $fillable = [
        'account_name',
        'account_id',
        'campaign_id',
        'campaign_name',
        'date_start',
        'spend',
        'clicks',
        'impressions',
        'reach',
        'ctr',
        'cpm',
        'cpc',
        'link_clicks',
        'leads',
        'purchases',
        'synced_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'date_start' => 'date',
        'spend' => 'float',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'reach' => 'integer',
        'ctr' => 'float',
        'cpm' => 'float',
        'cpc' => 'float',
        'link_clicks' => 'integer',
        'leads' => 'integer',
        'purchases' => 'integer',
        'synced_at' => 'datetime',
    ];
}
