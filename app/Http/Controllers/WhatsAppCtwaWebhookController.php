<?php

namespace App\Http\Controllers;

use App\Models\CtwaCapture;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Webhook da WhatsApp Cloud API (campo `messages`) do app Meta de captura CTWA.
 *
 * Lê o `referral` (ad_id + ctwa_clid) que o Meta anexa à PRIMEIRA mensagem de quem
 * clicou num anúncio click-to-WhatsApp e grava em `ctwa_capture`. O app fica inscrito
 * nas WABAs ao lado da SellFlux, então recebe todas as mensagens e status: só as
 * mensagens com `referral` interessam, o resto é descartado.
 */
class WhatsAppCtwaWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $expectedToken = (string) config('services.ctwa.verify_token');

        // Falha fechada: sem CTWA_VERIFY_TOKEN configurado, nenhuma verificação passa.
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

        Log::warning('CTWA Webhook: falha na verificação.', ['mode' => $mode]);

        return response('Forbidden', 403);
    }

    public function receive(Request $request)
    {
        $secret = (string) config('services.ctwa.app_secret');

        // Falha fechada: sem CTWA_APP_SECRET qualquer um conseguiria forjar a assinatura.
        if ($secret === '') {
            Log::error('CTWA Webhook: CTWA_APP_SECRET não configurado; requisição recusada.');
            return response('Forbidden', 403);
        }

        // HMAC sobre o corpo CRU (não sobre o JSON re-serializado).
        $raw = $request->getContent();
        $signature = (string) $request->header('X-Hub-Signature-256');
        $expected = 'sha256=' . hash_hmac('sha256', $raw, $secret);

        if ($signature === '' || !hash_equals($expected, $signature)) {
            Log::warning('CTWA Webhook: assinatura inválida.', ['ip' => $request->ip()]);
            return response('Forbidden', 403);
        }

        $payload = json_decode($raw, true);

        // Payload ilegível: reenviar não resolve, então 200 para o Meta não insistir.
        if (!is_array($payload)) {
            Log::warning('CTWA Webhook: payload não é JSON válido.');
            return response('', 200);
        }

        $rows = $this->extractReferrals($payload);

        if ($rows === []) {
            return response('', 200);
        }

        try {
            // Idempotente por wamid: um reenvio do Meta não duplica a linha.
            CtwaCapture::upsert($rows, ['wamid'], ['ctwa_clid', 'ad_id', 'updated_at']);
        } catch (QueryException $e) {
            // Erro de banco: 500 para o Meta reenviar depois (seguro pela idempotência).
            Log::error('CTWA Webhook: falha ao gravar referrals.', [
                'count' => count($rows),
                'error' => $e->getMessage(),
            ]);
            return response('', 500);
        }

        Log::info('CTWA Webhook: referrals gravados.', ['count' => count($rows)]);

        return response('', 200);
    }

    /**
     * Extrai só as mensagens que trazem `referral` (vieram de anúncio).
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractReferrals(array $payload): array
    {
        $rows = [];
        $now = Carbon::now();

        foreach ((array) ($payload['entry'] ?? []) as $entry) {
            $wabaId = $entry['id'] ?? null;

            foreach ((array) ($entry['changes'] ?? []) as $change) {
                if (($change['field'] ?? null) !== 'messages') {
                    continue;
                }

                $value = (array) ($change['value'] ?? []);
                $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

                foreach ((array) ($value['messages'] ?? []) as $message) {
                    $referral = $message['referral'] ?? null;
                    $wamid = $message['id'] ?? null;
                    $phone = preg_replace('/\D/', '', (string) ($message['from'] ?? ''));

                    if (!is_array($referral) || !is_string($wamid) || $wamid === '' || strlen($phone) < 8) {
                        continue;
                    }

                    $timestamp = $message['timestamp'] ?? null;

                    $rows[] = [
                        'phone' => substr($phone, 0, 24),
                        'phone_last8' => substr($phone, -8),
                        'ctwa_clid' => $this->str($referral['ctwa_clid'] ?? null, 600),
                        'ad_id' => $this->str($referral['source_id'] ?? null, 64),
                        'source_type' => $this->str($referral['source_type'] ?? null, 32),
                        'source_url' => $this->str($referral['source_url'] ?? null),
                        'headline' => $this->str($referral['headline'] ?? null),
                        'body' => $this->str($referral['body'] ?? null),
                        'waba_id' => $this->str($wabaId, 32),
                        'phone_number_id' => $this->str($phoneNumberId, 32),
                        'wamid' => substr($wamid, 0, 160),
                        'msg_ts' => is_numeric($timestamp)
                            ? Carbon::createFromTimestampUTC((int) $timestamp)->format('Y-m-d H:i:s')
                            : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        return $rows;
    }

    private function str(mixed $value, ?int $max = null): ?string
    {
        if (!is_scalar($value) || (string) $value === '') {
            return null;
        }

        return $max === null ? (string) $value : mb_substr((string) $value, 0, $max);
    }
}
