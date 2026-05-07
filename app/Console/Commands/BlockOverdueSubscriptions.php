<?php

namespace App\Console\Commands;

use App\Models\Farm;
use App\Models\Subscription;
use Illuminate\Console\Command;

class BlockOverdueSubscriptions extends Command
{
    protected $signature = 'subscriptions:block-overdue';
    protected $description = 'Bloqueia fazendas com assinatura past_due há mais de N dias (config asaas.block_after_days)';

    public function handle(): int
    {
        $days = (int) config('asaas.block_after_days', 7);
        $cutoff = now()->subDays($days);

        $subs = Subscription::query()
            ->where('status', Subscription::STATUS_PAST_DUE)
            ->where('updated_at', '<=', $cutoff)
            ->with('farm')
            ->get();

        $count = 0;
        foreach ($subs as $sub) {
            if (! $sub->farm) continue;
            if ($sub->farm->status === Farm::STATUS_BLOCKED) continue;

            $sub->farm->status = Farm::STATUS_BLOCKED;
            $sub->farm->save();
            $count++;

            $this->info("Bloqueada fazenda #{$sub->farm->id} - {$sub->farm->nome}");
        }

        $this->info("Concluído. {$count} fazenda(s) bloqueada(s).");
        return self::SUCCESS;
    }
}
