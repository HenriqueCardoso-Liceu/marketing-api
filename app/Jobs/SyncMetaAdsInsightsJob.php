<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaAdsService;

// app/Jobs/SyncMetaAdsInsightsJob.php

class SyncMetaAdsInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array  $account,
        private string $since,
        private string $until
    ) {}

    public function handle(MetaAdsService $service): void
    {
        $rows = $service->fetchInsights($this->account['id'], $this->since, $this->until);

        $records = collect($rows)->map(function ($row) {
            $actions = collect($row['actions'] ?? []);

            return [
                'account_id'    => $this->account['id'],
                'account_name'  => $this->account['name'], // <- novo
                'campaign_id'   => $row['campaign_id'] ?? null,
                'campaign_name' => $row['campaign_name'] ?? null,
                'date_start'    => $row['date_start'],
                'spend'         => $row['spend'] ?? 0,
                'clicks'        => $row['clicks'] ?? 0,
                'impressions'   => $row['impressions'] ?? 0,
                'reach'         => $row['reach'] ?? 0,
                'ctr'           => $row['ctr'] ?? 0,
                'cpm'           => $row['cpm'] ?? 0,
                'cpc'           => $row['cpc'] ?? 0,
                'link_clicks'   => $actions->firstWhere('action_type', 'link_click')['value'] ?? 0,
                'leads'         => $actions->firstWhere('action_type', 'lead')['value'] ?? 0,
                'purchases'     => $actions->firstWhere('action_type', 'purchase')['value'] ?? 0,
                'synced_at'     => now(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        })->chunk(200);

        foreach ($records as $chunk) {
            DB::table('meta_ads_insights')->upsert(
                $chunk->values()->toArray(),
                ['account_id', 'campaign_id', 'date_start'],
                [
                    'account_name',
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
                    'updated_at'
                ]
            );
        }
    }
}
