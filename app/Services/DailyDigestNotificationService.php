<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Goal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DailyDigestNotificationService
{
    public function morningPayload(): array
    {
        [$totalBalance, $activeGoals, $dailyTarget] = $this->baseMetrics();

        return [
            'title' => 'Point matinal Finance',
            'body' => sprintf(
                'Etat: %s EUR. Objectifs actifs: %d. Cible du jour: %s EUR.',
                number_format($totalBalance, 2, ',', ' '),
                $activeGoals,
                number_format($dailyTarget, 2, ',', ' ')
            ),
            'url' => url('/objectifs'),
            'icon' => url('/icons/icon.svg'),
            'badge' => url('/icons/icon.svg'),
            'tag' => 'fp-digest-morning',
        ];
    }

    public function eveningPayload(): array
    {
        [$totalBalance, $activeGoals] = $this->baseMetrics();

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $income = (float) Transaction::query()
            ->whereNull('transfer_group')
            ->where('type', 'income')
            ->whereBetween('spent_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $expenses = (float) Transaction::query()
            ->whereNull('transfer_group')
            ->where('type', 'expense')
            ->whereBetween('spent_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $reste = $income - $expenses;

        $tip = $reste >= 0
            ? 'Conseil: vire une partie du surplus vers ton epargne.'
            : 'Conseil: reduis une depense variable demain pour reequilibrer.';

        return [
            'title' => 'Bilan du soir Finance',
            'body' => sprintf(
                'Etat: %s EUR, objectifs: %d. %s',
                number_format($totalBalance, 2, ',', ' '),
                $activeGoals,
                $tip
            ),
            'url' => url('/recommandations'),
            'icon' => url('/icons/icon.svg'),
            'badge' => url('/icons/icon.svg'),
            'tag' => 'fp-digest-evening',
        ];
    }

    private function baseMetrics(): array
    {
        if (! $this->financeTablesReady()) {
            return [0.0, 0, 0.0];
        }

        $totalBalance = (float) Account::query()->sum('current_balance');
        $goals = Goal::query()->where('is_archived', false)->get();
        $activeGoals = $goals->count();

        $dailyTarget = 0.0;

        foreach ($goals as $goal) {
            $remaining = max(0.0, (float) $goal->target_amount - (float) $goal->current_amount);

            if (! $goal->target_date) {
                $dailyTarget += $remaining / 30;
                continue;
            }

            $targetDate = Carbon::parse($goal->target_date);
            $daysLeft = max(1, now()->startOfDay()->diffInDays($targetDate->startOfDay(), false));
            $dailyTarget += $remaining / max(1, $daysLeft);
        }

        return [$totalBalance, $activeGoals, round($dailyTarget, 2)];
    }

    private function financeTablesReady(): bool
    {
        return Schema::hasTable('accounts')
            && Schema::hasTable('goals')
            && Schema::hasTable('transactions');
    }
}
