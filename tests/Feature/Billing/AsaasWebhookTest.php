<?php

use App\Models\Farm;
use App\Models\Subscription;
use App\Models\WebhookEvent;

beforeEach(function () {
    config(['asaas.webhook_token' => 'test-token']);
});

function makeFarmWithSub(string $farmStatus, string $subStatus): array
{
    $farm = Farm::factory()->create(['status' => $farmStatus]);
    $sub = Subscription::create([
        'farm_id' => $farm->id,
        'status' => $subStatus,
        'asaas_subscription_id' => 'sub_test_' . $farm->id,
    ]);
    return [$farm, $sub];
}

it('rejects webhook with wrong token', function () {
    $this->postJson('/webhooks/asaas', ['event' => 'X'], ['asaas-access-token' => 'wrong'])
        ->assertStatus(401);
});

it('rejects webhook with empty payload', function () {
    $this->postJson('/webhooks/asaas', [], ['asaas-access-token' => 'test-token'])
        ->assertStatus(400);
});

it('PAYMENT_CONFIRMED activates subscription and farm', function () {
    [$farm, $sub] = makeFarmWithSub(Farm::STATUS_TRIAL, Subscription::STATUS_TRIAL);

    $this->postJson('/webhooks/asaas', [
        'id' => 'evt_1',
        'event' => 'PAYMENT_CONFIRMED',
        'payment' => ['subscription' => $sub->asaas_subscription_id, 'nextDueDate' => '2026-06-06'],
    ], ['asaas-access-token' => 'test-token'])->assertOk();

    expect($sub->fresh()->status)->toBe(Subscription::STATUS_ACTIVE);
    expect($sub->fresh()->current_period_end?->format('Y-m-d'))->toBe('2026-06-06');
    expect($farm->fresh()->status)->toBe(Farm::STATUS_ACTIVE);
});

it('PAYMENT_OVERDUE marks past_due', function () {
    [$farm, $sub] = makeFarmWithSub(Farm::STATUS_ACTIVE, Subscription::STATUS_ACTIVE);

    $this->postJson('/webhooks/asaas', [
        'id' => 'evt_2',
        'event' => 'PAYMENT_OVERDUE',
        'payment' => ['subscription' => $sub->asaas_subscription_id],
    ], ['asaas-access-token' => 'test-token'])->assertOk();

    expect($sub->fresh()->status)->toBe(Subscription::STATUS_PAST_DUE);
    expect($farm->fresh()->status)->toBe(Farm::STATUS_PAST_DUE);
});

it('SUBSCRIPTION_DELETED cancels and blocks farm', function () {
    [$farm, $sub] = makeFarmWithSub(Farm::STATUS_ACTIVE, Subscription::STATUS_ACTIVE);

    $this->postJson('/webhooks/asaas', [
        'id' => 'evt_3',
        'event' => 'SUBSCRIPTION_DELETED',
        'payment' => ['subscription' => $sub->asaas_subscription_id],
    ], ['asaas-access-token' => 'test-token'])->assertOk();

    expect($sub->fresh()->status)->toBe(Subscription::STATUS_CANCELED);
    expect($farm->fresh()->status)->toBe(Farm::STATUS_BLOCKED);
});

it('webhook is idempotent', function () {
    [$farm, $sub] = makeFarmWithSub(Farm::STATUS_TRIAL, Subscription::STATUS_TRIAL);

    $payload = [
        'id' => 'evt_idem',
        'event' => 'PAYMENT_CONFIRMED',
        'payment' => ['subscription' => $sub->asaas_subscription_id, 'nextDueDate' => '2026-06-06'],
    ];

    $this->postJson('/webhooks/asaas', $payload, ['asaas-access-token' => 'test-token'])->assertOk();
    $this->postJson('/webhooks/asaas', $payload, ['asaas-access-token' => 'test-token'])->assertOk();

    expect(WebhookEvent::where('event_id', 'evt_idem')->count())->toBe(1);
});

it('block-overdue command blocks farms past_due more than configured days', function () {
    config(['asaas.block_after_days' => 7]);

    $farm1 = Farm::factory()->create(['status' => Farm::STATUS_PAST_DUE]);
    $sub1 = Subscription::create(['farm_id' => $farm1->id, 'status' => Subscription::STATUS_PAST_DUE]);
    $sub1->updated_at = now()->subDays(8);
    $sub1->saveQuietly();

    $farm2 = Farm::factory()->create(['status' => Farm::STATUS_PAST_DUE]);
    $sub2 = Subscription::create(['farm_id' => $farm2->id, 'status' => Subscription::STATUS_PAST_DUE]);
    $sub2->updated_at = now()->subDays(2);
    $sub2->saveQuietly();

    $this->artisan('subscriptions:block-overdue')->assertExitCode(0);

    expect($farm1->fresh()->status)->toBe(Farm::STATUS_BLOCKED);
    expect($farm2->fresh()->status)->toBe(Farm::STATUS_PAST_DUE);
});
