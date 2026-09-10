<?php

namespace App\Http\Controllers\Api;

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
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if (
            $mode === 'subscribe' &&
            $token === config('services.meta.verify_token')
        ) {
            return response($challenge, 200);
        }

        Log::warning('Meta Webhook: falha na verificação.', [
            'mode' => $mode,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Recebe os eventos de Lead Ads enviados pela Meta.
     */
    public function receive(Request $request)
    {
        $payload = $request->all();

        Log::info('Meta Lead Webhook recebido.', [
            'payload' => $payload,
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
;
