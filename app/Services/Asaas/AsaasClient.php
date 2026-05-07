<?php

namespace App\Services\Asaas;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AsaasClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
    ) {}

    public static function default(): self
    {
        return new self(
            apiKey: (string) config('asaas.api_key'),
            baseUrl: (string) config('asaas.base_url'),
        );
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function createCustomer(array $data): array
    {
        return $this->request()->post('/customers', $data)->throw()->json();
    }

    public function createSubscription(array $data): array
    {
        return $this->request()->post('/subscriptions', $data)->throw()->json();
    }

    public function getSubscription(string $id): array
    {
        return $this->request()->get("/subscriptions/{$id}")->throw()->json();
    }

    public function cancelSubscription(string $id): array
    {
        return $this->request()->delete("/subscriptions/{$id}")->throw()->json();
    }

    private function request(): PendingRequest
    {
        return Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($this->baseUrl)->timeout(15);
    }
}
