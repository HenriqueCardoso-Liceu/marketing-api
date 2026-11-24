<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege rotas administrativas com uma chave de API (header X-Api-Key ou Authorization: Bearer).
 *
 * INTERNAL_API_KEY_ENFORCE=true (padrão): responde 401 para quem não enviar a chave correta.
 * INTERNAL_API_KEY_ENFORCE=false: só registra no log (modo de descoberta; não usar em produção).
 * Sem INTERNAL_API_KEY configurada, nenhuma chave é válida (falha fechada).
 */
class ApiKeyGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.internal_api.key');
        $provided = (string) ($request->header('X-Api-Key') ?? $request->bearerToken() ?? '');

        $valid = $expected !== '' && $provided !== '' && hash_equals($expected, $provided);

        if ($valid) {
            return $next($request);
        }

        Log::warning('ApiKeyGuard: chamada sem chave válida', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),
            'user_agent' => substr((string) $request->userAgent(), 0, 200),
            'enforced' => (bool) config('services.internal_api.enforce'),
        ]);

        if (config('services.internal_api.enforce')) {
            return response()->json(['message' => 'Não autorizado.'], 401);
        }

        return $next($request);
    }
}
