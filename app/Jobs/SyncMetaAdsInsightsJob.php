<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Services\MetaAdsService;

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
            $actions = $row['actions'] ?? [];

            return [
                'account_id'    => $this->account['id'],
                'account_name'  => $this->account['name'],
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
                'link_clicks'   => $this->getActionValue($actions, 'link_click'),
                'leads'         => $this->getLeads($actions),         // <- corrigido
                'whatsapp'      => $this->getActionValue($actions,    // <- novo
                                   'onsite_conversion.messaging_conversation_started_7d'),
                'purchases'     => $this->getActionValue($actions, 'purchase'),
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
                    'whatsapp',   // <- novo
                    'purchases',
                    'synced_at',
                    'updated_at',
                ]
            );
        }
    }

    // ── Helpers de actions ────────────────────────────────────

    private function getActionValue(array $actions, string $type): float
    {
        foreach ($actions as $action) {
            if ($action['action_type'] === $type) {
                return (float) $action['value'];
            }
        }
        return 0;
    }

    private function getLeads(array $actions): float
    {
        $pixel    = $this->getActionValue($actions, 'offsite_conversion.fb_pixel_lead');
        $form     = $this->getActionValue($actions, 'onsite_conversion.lead_grouped');
        //$workshop = $this->getActionValue($actions, 'offsite_conversion.custom.1258896832705415');

        // Math.max evita dupla contagem nas campanhas Workshop
        //return max($pixel + $form, $workshop);

        // Workshop é uma conversão personalizada DENTRO do pixel, não somamos separadamente
        return $pixel + $form;
    }
}
