<?php

use Illuminate\Support\Facades\Http;

class MetaAdsService
{
    private string $baseUrl = 'https://graph.facebook.com/v21.0';

    public function fetchInsights(
        string $accountId,
        string $since,
        string $until,
        string $level = 'campaign'
    ): array {
        $fields = $level === 'campaign'
            ? 'date_start,campaign_id,campaign_name,spend,clicks,impressions,reach,ctr,cpm,cpc,actions'
            : 'date_start,spend,clicks,impressions,reach,ctr,cpm,cpc,actions';

        $allData = [];
        $url = "{$this->baseUrl}/act_{$accountId}/insights";

        do {
            $response = Http::get($url, [
                'fields' => $fields,
                'time_increment' => 1,
                'level' => $level,
                'time_range' => json_encode(['since' => $since, 'until' => $until]),
                'limit' => 500,
                'access_token' => config('services.meta.access_token'),
            ]);

            $response->throw(); // lança exception em erro HTTP

            $body = $response->json();
            $allData = array_merge($allData, $body['data'] ?? []);

            // paginação automática
            $url = $body['paging']['next'] ?? null;
        } while ($url);

        return $allData;
    }
}
