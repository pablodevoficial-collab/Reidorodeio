<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FacebookDataDeletionController extends Controller
{
    public function instructions()
    {
        return response(
            'Facebook data deletion callback active. Use POST on this endpoint with signed_request.',
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    public function callback(Request $request): JsonResponse
    {
        $signedRequest = (string) $request->input('signed_request', '');
        if ($signedRequest !== '') {
            $this->decodeSignedRequest($signedRequest);
        }

        $code = strtoupper(Str::random(10));
        Cache::put("facebook_data_deletion:{$code}", [
            'created_at' => now()->toDateTimeString(),
            'ip' => $request->ip(),
        ], now()->addDays(30));

        $baseUrl = rtrim((string) config('app.url'), '/');
        $statusUrl = $baseUrl . '/facebook/data-deletion/status/' . rawurlencode($code);

        return response()->json([
            'url' => $statusUrl,
            'confirmation_code' => $code,
        ]);
    }

    public function status(string $code)
    {
        $exists = Cache::has("facebook_data_deletion:{$code}");
        if (!$exists) {
            return response('Codigo de confirmacao invalido.', 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        return response("Solicitacao de exclusao de dados recebida. Codigo: {$code}", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeSignedRequest(string $signedRequest): array
    {
        $parts = explode('.', $signedRequest, 2);
        if (count($parts) !== 2) {
            throw new \RuntimeException('signed_request invalido.');
        }

        [$encodedSignature, $encodedPayload] = $parts;
        $signature = $this->base64UrlDecode($encodedSignature);
        $payload = $this->base64UrlDecode($encodedPayload);

        $data = json_decode($payload, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Payload signed_request invalido.');
        }

        $algorithm = strtoupper((string) ($data['algorithm'] ?? ''));
        if ($algorithm !== 'HMAC-SHA256') {
            throw new \RuntimeException('Algoritmo signed_request invalido.');
        }

        $secret = (string) config('services.facebook.client_secret');
        if ($secret !== '') {
            $expected = hash_hmac('sha256', $encodedPayload, $secret, true);
            if (!hash_equals($expected, $signature)) {
                throw new \RuntimeException('Assinatura signed_request invalida.');
            }
        }

        return $data;
    }

    private function base64UrlDecode(string $input): string
    {
        $replaced = str_replace(['-', '_'], ['+', '/'], $input);
        $padding = strlen($replaced) % 4;
        if ($padding > 0) {
            $replaced .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($replaced, true);
        if ($decoded === false) {
            throw new \RuntimeException('Falha ao decodificar base64url.');
        }

        return $decoded;
    }
}
