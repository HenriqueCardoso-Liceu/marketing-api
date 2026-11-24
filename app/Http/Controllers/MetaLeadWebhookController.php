<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaLeadWebhookController extends Controller
{
    /**
     * Verificação inicial do Webhook feita pela Meta.
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $expectedToken = (string) config('services.meta.verify_token');

        // Falha fechada: sem META_VERIFY_TOKEN configurado, nenhuma verificação passa.
        if (
            $mode === 'subscribe'
            && $expectedToken !== ''
            && is_string($token)
            && hash_equals($expectedToken, $token)
            && is_string($challenge)
            && ctype_digit($challenge)
        ) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta Webhook: falha na verificação.', ['mode' => $mode]);

        return response('Forbidden', 403);
    }

    /**
     * Recebe os eventos de Lead Ads enviados pela Meta.
     */
    public function receive(Request $request)
    {
        $secret = (string) config('services.meta.app_secret');

        // Falha fechada: sem META_APP_SECRET o HMAC seria calculado com chave vazia
        // e qualquer um conseguiria forjar a assinatura.
        if ($secret === '') {
            Log::error('Meta Webhook: META_APP_SECRET não configurado; requisição recusada.');
            return response('Forbidden', 403);
        }

        $signature = (string) $request->header('X-Hub-Signature-256');
        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature === '' || !hash_equals($expected, $signature)) {
            Log::warning('Meta Webhook: assinatura inválida.', ['ip' => $request->ip()]);
            return response('Forbidden', 403);
        }

        // Não grava o payload inteiro no log; só os IDs (abaixo).
        Log::info('Meta Lead Webhook recebido.', [
            'entries' => count((array) $request->input('entry', [])),
        ]);

        /*
         * A Meta envia o leadgen_id dentro de:
         *
         * entry[0]
         *   -> changes[0]
         *      -> value
         *         -> leadgen_id
         */

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {

                if (($change['field'] ?? null) !== 'leadgen') {
                    continue;
                }

                $value = $change['value'] ?? [];

                $leadgenId = $value['leadgen_id'] ?? null;
                $formId = $value['form_id'] ?? null;
                $pageId = $value['page_id'] ?? null;
                $createdTime = $value['created_time'] ?? null;

                Log::info('Meta Lead identificado.', [
                    'leadgen_id' => $leadgenId,
                    'form_id' => $formId,
                    'page_id' => $pageId,
                    'created_time' => $createdTime,
                ]);

                /*
                 * A próxima etapa será enviar o $leadgenId
                 * para um Job/Service que fará:
                 *
                 * GET /{leadgen_id}
                 *
                 * na Graph API da Meta e depois salvará
                 * o lead no banco.
                 */
            }
        }

        /*
         * É importante responder 200 para a Meta rapidamente.
         */
        return response()->json([
            'success' => true,
        ], 200);
    }
}
