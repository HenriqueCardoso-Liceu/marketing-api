<?php

namespace App\Console\Commands;

use App\Jobs\SyncMetaAdsInsightsJob;
use Illuminate\Console\Command;

class SyncMetaAdsInsights extends Command
{
    protected $signature = 'meta:sync-insights {--days=2 : Quantos dias retroativos}';
    protected $description = 'Sincroniza insights do Meta Ads para todas as contas';

    public function handle(): void
    {
        $accounts = config('services.meta.accounts');
        $days = (int) $this->option('days');

        foreach ($accounts as $account) {
            SyncMetaAdsInsightsJob::dispatch(
                $account,
                now()->subDays($days)->toDateString(),
                now()->subDay()->toDateString()
            );
            $this->info("Job disparado: {$account['name']}");
        }

        $this->info('Todos os jobs foram enfileirados.');
    }
}
