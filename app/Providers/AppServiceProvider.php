<?php

namespace App\Providers;

use App\Models\Registration;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureTrustedProxies();
        $this->configureRateLimiting();
        $this->configureRouteBindings();
    }

    /**
     * {registration} só resolve dentro da janela de edição após a criação.
     * Fora dela responde 404 antes de qualquer validação, para um link antigo ou
     * vazado não permitir ler nem alterar o cadastro.
     */
    private function configureRouteBindings(): void
    {
        Route::bind('registration', function (string $value) {
            $window = (int) config('security.registration_edit_window_minutes');

            return Registration::query()
                ->whereKey($value)
                ->where('created_at', '>=', now()->subMinutes($window))
                ->firstOrFail();
        });
    }

    /**
     * Atrás de proxy/Cloudflare, sem isto o $request->ip() é o IP do proxy e o
     * rate limit passa a valer para todos os visitantes juntos.
     */
    private function configureTrustedProxies(): void
    {
        $proxies = config('security.trusted_proxies');

        if (filled($proxies)) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }
    }

    private function configureRateLimiting(): void
    {
        // Formulários públicos das LPs: um responsável envia 1–2 vezes; 10/min cobre
        // reenvios e redes compartilhadas (ex.: Wi-Fi da escola) sem permitir spam em massa.
        RateLimiter::for('public-forms', function (Request $request) {
            return [
                Limit::perMinute(config('security.rate_limits.public_forms_per_minute'))->by($request->ip()),
                Limit::perHour(config('security.rate_limits.public_forms_per_hour'))->by($request->ip()),
            ];
        });

        // Rotas internas (painel) e webhook da Meta.
        RateLimiter::for('internal', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.internal_per_minute'))->by($request->ip());
        });

        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.webhooks_per_minute'))->by($request->ip());
        });
    }
}
