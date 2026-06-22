<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MercadoPagoService
{
    public function createPreference(array $payload): array
    {
        $payload = $this->normalizePayerData($payload);

        try {
            $response = $this->request()
                ->post($this->baseUrl() . '/checkout/preferences', $payload);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Create Preference', $e, [
                'payload' => $payload,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);
            abort(502, 'Falha ao criar preferência de pagamento.');
        }

        return $response->json();
    }

    public function fetchPayment(string $paymentId): array
    {
        try {
            $response = $this->request()
                ->get($this->baseUrl() . '/v1/payments/' . $paymentId);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Fetch Payment', $e, [
                'payment_id' => $paymentId,
            ]);
        }

        if (!$response->successful()) {
            abort(502, 'Falha ao consultar pagamento Mercado Pago.');
        }

        return $response->json();
    }

    public function createPixPayment(array $payload): array
    {
        $payload = $this->normalizePayerData($payload);

        // Gerar idempotency key única
        $idempotencyKey = Str::uuid()->toString();

        try {
            $response = $this->request($idempotencyKey)
                ->post($this->baseUrl() . '/v1/payments', $payload);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Create PIX Payment', $e, [
                'payload' => $payload,
                'idempotency_key' => $idempotencyKey,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago PIX Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
                'idempotency_key' => $idempotencyKey,
            ]);
            throw new \Exception('Falha ao criar pagamento PIX: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Buscar pagamentos pelo external_reference no MercadoPago.
     * Útil quando provider_payment_id não foi salvo localmente.
     */
    public function searchPaymentsByExternalReference(string $externalReference): ?array
    {
        try {
            $response = $this->request()
                ->get($this->baseUrl() . '/v1/payments/search', [
                    'external_reference' => $externalReference,
                    'sort' => 'date_created',
                    'criteria' => 'desc',
                    'limit' => 1,
                ]);

            if (!$response->successful()) {
                \Log::warning('MercadoPago Search Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'external_reference' => $externalReference,
                ]);
                return null;
            }

            $data = $response->json();
            $results = $data['results'] ?? [];

            return !empty($results) ? $results[0] : null;
        } catch (\Exception $e) {
            \Log::warning('MercadoPago Search Exception', [
                'error' => $e->getMessage(),
                'external_reference' => $externalReference,
            ]);
            return null;
        }
    }

    public function cancelPayment(string $paymentId): array
    {
        try {
            $response = $this->request()
                ->put($this->baseUrl() . '/v1/payments/' . $paymentId, [
                    'status' => 'cancelled'
                ]);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Cancel Payment', $e, [
                'payment_id' => $paymentId,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Cancel Payment Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payment_id' => $paymentId,
            ]);
            throw new \Exception('Falha ao cancelar pagamento: ' . $response->body());
        }

        return $response->json();
    }

    public function buildExternalReference(int $roomId, int $userId, string $role): string
    {
        return 'x1_room:' . $roomId . '|user:' . $userId . '|role:' . $role . '|' . Str::random(6);
    }

    /**
     * Cria uma assinatura recorrente (preapproval) no Mercado Pago
     * Documentação: https://www.mercadopago.com.br/developers/pt/reference/subscriptions/_preapproval/post
     */
    public function createPreapproval(array $payload): array
    {
        $payload = $this->normalizePayerData($payload);

        try {
            $response = $this->request()
                ->post($this->baseUrl() . '/preapproval', $payload);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Create Preapproval', $e, [
                'payload' => $payload,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Preapproval Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);
            throw new \Exception('Falha ao criar assinatura: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Busca dados de uma assinatura (preapproval)
     */
    public function getPreapproval(string $preapprovalId): array
    {
        try {
            $response = $this->request()
                ->get($this->baseUrl() . '/preapproval/' . $preapprovalId);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Get Preapproval', $e, [
                'preapproval_id' => $preapprovalId,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Get Preapproval Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'preapproval_id' => $preapprovalId,
            ]);
            throw new \Exception('Falha ao buscar assinatura: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Cancela uma assinatura (preapproval)
     */
    public function cancelPreapproval(string $preapprovalId): array
    {
        try {
            $response = $this->request()
                ->put($this->baseUrl() . '/preapproval/' . $preapprovalId, [
                    'status' => 'cancelled'
                ]);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Cancel Preapproval', $e, [
                'preapproval_id' => $preapprovalId,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Cancel Preapproval Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'preapproval_id' => $preapprovalId,
            ]);
            throw new \Exception('Falha ao cancelar assinatura: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Cria um reembolso
     */
    public function createRefund(string $paymentId, ?float $amount = null): array
    {
        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = $amount;
        }

        try {
            $response = $this->request()
                ->post($this->baseUrl() . '/v1/payments/' . $paymentId . '/refunds', $payload);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Create Refund', $e, [
                'payment_id' => $paymentId,
                'amount' => $amount,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Refund Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payment_id' => $paymentId,
                'amount' => $amount,
            ]);
            throw new \Exception('Falha ao criar reembolso: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Processa pagamento com card_token (Checkout Transparente)
     * Usado para cobranças únicas ou validação de cartão
     */
    public function processCardPayment(array $payload): array
    {
        $payload = $this->normalizePayerData($payload);

        $idempotencyKey = Str::uuid()->toString();

        try {
            $response = $this->request($idempotencyKey)
                ->post($this->baseUrl() . '/v1/payments', $payload);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Process Card Payment', $e, [
                'payload' => array_merge($payload, ['token' => '***REDACTED***']),
                'idempotency_key' => $idempotencyKey,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Card Payment Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => array_merge($payload, ['token' => '***REDACTED***']),
                'idempotency_key' => $idempotencyKey,
            ]);
            
            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? 'Falha ao processar pagamento';
            
            // Mapeia erros comuns para mensagens amigáveis
            if (isset($errorBody['cause'])) {
                foreach ($errorBody['cause'] as $cause) {
                    $code = $cause['code'] ?? '';
                    if (str_contains($code, 'cc_rejected')) {
                        $errorMessage = match(true) {
                            str_contains($code, 'insufficient_amount') => 'Saldo insuficiente no cartão',
                            str_contains($code, 'bad_filled') => 'Dados do cartão incorretos',
                            str_contains($code, 'high_risk') => 'Pagamento não autorizado por segurança',
                            str_contains($code, 'max_attempts') => 'Limite de tentativas atingido',
                            str_contains($code, 'duplicated_payment') => 'Pagamento duplicado detectado',
                            str_contains($code, 'card_disabled') => 'Cartão desabilitado',
                            str_contains($code, 'call_for_authorize') => 'Ligue para o banco para autorizar',
                            default => 'Cartão recusado. Tente outro cartão.',
                        };
                        break;
                    }
                }
            }
            
            throw new \Exception($errorMessage);
        }

        return $response->json();
    }

    /**
     * Cria assinatura recorrente com cartão tokenizado
     * Usado para trial com cobrança automática futura
     */
    public function createCardSubscription(array $payload): array
    {
        $payload = $this->normalizePayerData($payload);

        try {
            $response = $this->request()
                ->post($this->baseUrl() . '/preapproval', $payload);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Create Card Subscription', $e, [
                'payload' => array_merge($payload, ['card_token_id' => '***REDACTED***']),
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Card Subscription Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => array_merge($payload, ['card_token_id' => '***REDACTED***']),
            ]);
            throw new \Exception('Falha ao criar assinatura: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Salva cartão do cliente para cobranças futuras
     */
    public function saveCard(string $cardToken, string $customerId): array
    {
        try {
            $response = $this->request()
                ->post($this->baseUrl() . '/v1/customers/' . $customerId . '/cards', [
                    'token' => $cardToken,
                ]);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Save Card', $e, [
                'customer_id' => $customerId,
            ]);
        }

        if (!$response->successful()) {
            \Log::error('MercadoPago Save Card Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Falha ao salvar cartão');
        }

        return $response->json();
    }

    /**
     * Cria ou busca customer no Mercado Pago
     */
    public function getOrCreateCustomer(string $email, ?string $firstName = null, ?string $lastName = null): array
    {
        $email = $this->sanitizeEmail($email);

        // Busca customer existente
        try {
            $searchResponse = $this->request()
                ->get($this->baseUrl() . '/v1/customers/search', [
                    'email' => $email,
                ]);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Search Customer', $e, [
                'email' => $email,
            ]);
        }

        if ($searchResponse->successful()) {
            $results = $searchResponse->json()['results'] ?? [];
            if (!empty($results)) {
                return $results[0];
            }
        }

        // Cria novo customer
        try {
            $createResponse = $this->request()
                ->post($this->baseUrl() . '/v1/customers', [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
        } catch (ConnectionException $e) {
            $this->throwConnectionException('Create Customer', $e, [
                'email' => $email,
            ]);
        }

        if (!$createResponse->successful()) {
            \Log::error('MercadoPago Create Customer Error', [
                'status' => $createResponse->status(),
                'body' => $createResponse->body(),
            ]);
            throw new \Exception('Falha ao criar customer');
        }

        return $createResponse->json();
    }

    private function normalizePayerData(array $payload): array
    {
        if (array_key_exists('payer_email', $payload)) {
            $payload['payer_email'] = $this->sanitizeEmail((string) $payload['payer_email'], $payload['external_reference'] ?? null);
        }

        if (!isset($payload['payer']) || !is_array($payload['payer'])) {
            return $payload;
        }

        $payer = $payload['payer'];

        if (array_key_exists('email', $payer)) {
            $payer['email'] = $this->sanitizeEmail((string) $payer['email'], $payload['external_reference'] ?? null);
        }

        if (empty($payer['first_name']) && empty($payer['name'])) {
            $payer['first_name'] = 'Usuario';
        }

        $payload['payer'] = $payer;
        return $payload;
    }

    private function request(?string $idempotencyKey = null): PendingRequest
    {
        $request = Http::withToken($this->token())
            ->acceptJson()
            ->timeout((int) config('services.mercadopago.timeout', 30))
            ->connectTimeout((int) config('services.mercadopago.connect_timeout', 10))
            ->retry(2, 200);

        if ($idempotencyKey) {
            $request = $request->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ]);
        }

        if (!$this->shouldVerifySsl()) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function token(): string
    {
        $token = (string) config('services.mercadopago.access_token');

        if ($token === '') {
            abort(500, 'Mercado Pago access token não configurado.');
        }

        return $token;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.mercadopago.base_url'), '/');
    }

    private function shouldVerifySsl(): bool
    {
        return (bool) config('services.mercadopago.verify_ssl', true);
    }

    private function throwConnectionException(string $operation, ConnectionException $e, array $context = []): never
    {
        \Log::error("MercadoPago {$operation} Connection Error", array_merge([
            'error' => $e->getMessage(),
            'ssl_verification' => $this->shouldVerifySsl(),
        ], $context));

        $message = $e->getMessage();
        $messageLower = strtolower($message);

        if ($this->shouldVerifySsl() && (str_contains($message, 'cURL error 60') || str_contains($messageLower, 'ssl certificate'))) {
            throw new \Exception(
                'Falha na conexão SSL com o Mercado Pago. No ambiente local, configure MERCADOPAGO_VERIFY_SSL=false ou instale os certificados CA do PHP.',
                0,
                $e
            );
        }

        throw new \Exception('Falha ao conectar com o Mercado Pago: ' . $message, 0, $e);
    }

    private function sanitizeEmail(string $email, ?string $seed = null): string
    {
        $normalized = strtolower(trim($email));

        $invalidDomain = str_ends_with($normalized, '@deleted.local')
            || str_ends_with($normalized, '@deleted.invalid');

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) && !$invalidDomain) {
            return $normalized;
        }

        $suffix = $seed ? substr(md5($seed), 0, 10) : Str::lower(Str::random(10));
        return "noreply+{$suffix}@reidorodeio.com.br";
    }
}
