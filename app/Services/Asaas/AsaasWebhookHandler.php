<?php

namespace App\Services\Asaas;

use App\Models\Farm;
use App\Models\Subscription;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsaasWebhookHandler
{
    /**
     * Recebe payload completo do webhook Asaas e atualiza estado da assinatura.
     * Idempotente via webhook_events.event_id.
     */
    public function handle(array $payload): void
    {
        $eventId = (string) ($payload['id'] ?? $payload['event'].':'.($payload['payment']['id'] ?? '').':'.($payload['dateCreated'] ?? ''));
        $eventType = (string) ($payload['event'] ?? 'unknown');

        // Idempotência: se já processado, não reprocessa.
        $event = WebhookEvent::firstOrCreate(
            ['event_id' => $eventId],
            [
                'provider' => 'asaas',
                'event_type' => $eventType,
                'payload' => $payload,
            ]
        );

        if ($event->processed_at) {
            return;
        }

        try {
            DB::transaction(function () use ($payload, $eventType, $event) {
                $this->dispatch($eventType, $payload);
                $event->processed_at = now();
                $event->save();
            });
        } catch (\Throwable $e) {
            $event->error = substr($e->getMessage(), 0, 1000);
            $event->save();
            Log::error('asaas-webhook failed', ['event_id' => $event->event_id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function dispatch(string $eventType, array $payload): void
    {
        $payment = $payload['payment'] ?? [];
        $subscriptionId = $payment['subscription'] ?? null;

        if (! $subscriptionId) {
            // Eventos sem subscription (ex: checkout) ignorados nesta fase
            return;
        }

        $subscription = Subscription::where('asaas_subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            Log::warning('asaas-webhook: subscription not found', ['asaas_subscription_id' => $subscriptionId]);
            return;
        }

        match ($eventType) {
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => $this->markActive($subscription, $payment),
            'PAYMENT_OVERDUE' => $this->markPastDue($subscription),
            'PAYMENT_REFUNDED', 'PAYMENT_DELETED' => $this->markPastDue($subscription),
            'SUBSCRIPTION_DELETED' => $this->markCanceled($subscription),
            default => null,
        };
    }

    private function markActive(Subscription $sub, array $payment): void
    {
        $sub->status = Subscription::STATUS_ACTIVE;
        if (! empty($payment['nextDueDate'])) {
            $sub->current_period_end = $payment['nextDueDate'];
        }
        $sub->save();

        $farm = $sub->farm;
        if ($farm && $farm->status !== Farm::STATUS_ACTIVE) {
            $farm->status = Farm::STATUS_ACTIVE;
            $farm->save();
        }
    }

    private function markPastDue(Subscription $sub): void
    {
        $sub->status = Subscription::STATUS_PAST_DUE;
        $sub->save();

        $farm = $sub->farm;
        if ($farm && $farm->status === Farm::STATUS_ACTIVE) {
            $farm->status = Farm::STATUS_PAST_DUE;
            $farm->save();
        }
    }

    private function markCanceled(Subscription $sub): void
    {
        $sub->status = Subscription::STATUS_CANCELED;
        $sub->save();

        $farm = $sub->farm;
        if ($farm) {
            $farm->status = Farm::STATUS_BLOCKED;
            $farm->save();
        }
    }
}
