<?php

namespace App\Actions\Admin;

use App\Exceptions\DomainException;
use App\Models\Farm;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Muda o status de uma assinatura — operação só de root.
 * Sincroniza Subscription.status com Farm.status (mapeamento 1:1, mesmo valor)
 * pra middleware e dashboard ficarem coerentes.
 *
 * Transições válidas:
 * - trial      → active | partner | blocked | canceled
 * - active     → partner | blocked | canceled | past_due
 * - past_due   → active  | partner | blocked | canceled
 * - blocked    → active  | partner | trial
 * - canceled   → active  | partner | trial
 * - partner    → trial   | active | blocked | canceled
 *
 * Efeitos colaterais por destino:
 * - partner : zera asaas_subscription_id e current_period_end (sem cobrança/expiração)
 * - trial   : seta trial_ends_at = hoje + FARM_TRIAL_DAYS
 * - active  : mantém ids do Asaas (gerados pelo webhook)
 * - blocked/canceled : preserva ids do Asaas pra auditoria
 */
class ChangeSubscriptionStatusAction
{
    public function execute(Farm $farm, string $newStatus, User $actor): Subscription
    {
        if (! $actor->isRoot()) {
            throw new DomainException('Apenas o usuário root pode mudar o plano de uma fazenda.');
        }

        $allowedStatuses = [
            Subscription::STATUS_TRIAL,
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_PAST_DUE,
            Subscription::STATUS_CANCELED,
            Subscription::STATUS_BLOCKED,
            Subscription::STATUS_PARTNER,
        ];

        if (! in_array($newStatus, $allowedStatuses, true)) {
            throw new DomainException("Status inválido: {$newStatus}");
        }

        $subscription = $farm->subscription;
        if (! $subscription) {
            throw new DomainException('Esta fazenda não tem assinatura cadastrada.');
        }

        return DB::transaction(function () use ($farm, $subscription, $newStatus) {
            // Subscription: aplica efeitos colaterais do destino
            $subscription->status = $newStatus;

            if ($newStatus === Subscription::STATUS_PARTNER) {
                $subscription->current_period_end = null;
                $subscription->asaas_subscription_id = null;
            } elseif ($newStatus === Subscription::STATUS_TRIAL) {
                $days = (int) config('app.farm_trial_days', env('FARM_TRIAL_DAYS', 14));
                $subscription->trial_ends_at = now()->addDays($days);
            }

            $subscription->save();

            // Farm: sincroniza status (1:1) — middleware EnsureFarmActive usa Farm.status
            $farm->status = $newStatus === Subscription::STATUS_PAST_DUE
                ? Farm::STATUS_PAST_DUE
                : $newStatus;
            if ($newStatus === Subscription::STATUS_TRIAL) {
                $farm->trial_ends_at = $subscription->trial_ends_at;
            }
            $farm->save();

            return $subscription->fresh();
        });
    }
}
