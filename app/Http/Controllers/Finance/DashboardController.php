<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function overview(Request $request): View
    {
        return view('pages.overview', $this->buildViewData($request));
    }

    public function accountsPage(Request $request): View
    {
        return view('pages.accounts', $this->buildViewData($request));
    }

    public function transactionsPage(Request $request): View
    {
        return view('pages.transactions', $this->buildViewData($request));
    }

    public function goalsPage(Request $request): View
    {
        return view('pages.goals', $this->buildViewData($request));
    }

    public function recommendationsPage(Request $request): View
    {
        return view('pages.recommendations', $this->buildViewData($request));
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'label' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:24'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $installments = (int) ($validated['installments'] ?? 1);
        $baseDate = Carbon::parse($validated['spent_at']);

        if ($installments === 1) {
            Transaction::create([
                'type' => $validated['type'],
                'account_id' => $validated['account_id'] ?? null,
                'category_id' => $validated['category_id'],
                'label' => $validated['label'],
                'amount' => $validated['amount'],
                'spent_at' => $validated['spent_at'],
                'notes' => $validated['notes'] ?? null,
            ]);

            return $this->redirectWithNotification($request, 'Transaction enregistree.');
        }

        $groupId = (string) Str::uuid();
        $unitAmount = round(((float) $validated['amount']) / $installments, 2);

        for ($index = 1; $index <= $installments; $index++) {
            $amount = $index === $installments
                ? round(((float) $validated['amount']) - ($unitAmount * ($installments - 1)), 2)
                : $unitAmount;

            Transaction::create([
                'type' => $validated['type'],
                'account_id' => $validated['account_id'] ?? null,
                'category_id' => $validated['category_id'],
                'label' => $validated['label'].' ('.$index.'/'.$installments.')',
                'amount' => $amount,
                'spent_at' => $baseDate->copy()->addMonths($index - 1)->toDateString(),
                'notes' => $validated['notes'] ?? null,
                'split_group' => $groupId,
                'split_number' => $index,
                'split_total' => $installments,
            ]);
        }

        return $this->redirectWithNotification($request, 'Paiement fractionne en '.$installments.' lignes.');
    }


    public function updateTransaction(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'label' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $transaction->update([
            'type' => $validated['type'],
            'account_id' => $validated['account_id'] ?? null,
            'category_id' => $validated['category_id'],
            'label' => $validated['label'],
            'amount' => $validated['amount'],
            'spent_at' => $validated['spent_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->redirectWithNotification($request, 'Transaction modifiee.', 'success');
    }

    public function destroyTransaction(Request $request, Transaction $transaction): RedirectResponse
    {
        $deleteGroup = (bool) $request->boolean('delete_group');
        $deleted = 1;

        if ($deleteGroup && $transaction->split_group) {
            $deleted = Transaction::query()->where('split_group', $transaction->split_group)->delete();
        } else {
            $transaction->delete();
        }

        $message = $deleted > 1
            ? 'Paiement fractionne supprime ('.$deleted.' lignes).'
            : 'Transaction supprimee.';

        return $this->redirectWithNotification($request, $message, 'warning');
    }
    public function storeTransfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_account_id' => ['required', 'exists:accounts,id'],
            'to_account_id' => ['required', 'exists:accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_at' => ['required', 'date'],
            'label' => ['required', 'string', 'max:120'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $expenseTransfer = Category::firstOrCreate(['name' => 'Transfert sortant', 'type' => 'expense']);
        $incomeTransfer = Category::firstOrCreate(['name' => 'Transfert entrant', 'type' => 'income']);

        $group = (string) Str::uuid();

        Transaction::create([
            'account_id' => $validated['from_account_id'],
            'category_id' => $expenseTransfer->id,
            'type' => 'expense',
            'label' => 'Transfert: '.$validated['label'],
            'amount' => $validated['amount'],
            'spent_at' => $validated['spent_at'],
            'notes' => 'Transfert interne',
            'transfer_group' => $group,
        ]);

        Transaction::create([
            'account_id' => $validated['to_account_id'],
            'category_id' => $incomeTransfer->id,
            'type' => 'income',
            'label' => 'Transfert: '.$validated['label'],
            'amount' => $validated['amount'],
            'spent_at' => $validated['spent_at'],
            'notes' => 'Transfert interne',
            'transfer_group' => $group,
        ]);

        return $this->redirectWithNotification($request, 'Transfert enregistre.');
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        Budget::updateOrCreate(
            [
                'category_id' => $validated['category_id'],
                'month' => Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()->toDateString(),
            ],
            ['amount' => $validated['amount']]
        );

        return $this->redirectWithNotification($request, 'Budget mis a jour.');
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:saving,investment'],
            'cadence' => ['required', 'in:monthly,quarterly,semiannual,annual'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->toDateString());
        $targetDate = isset($validated['target_date']) ? Carbon::parse($validated['target_date']) : null;

        if ($targetDate && $targetDate->lessThan($startDate)) {
            throw ValidationException::withMessages([
                'target_date' => 'La date cible doit etre apres la date de depart.',
            ]);
        }

        $currentAmount = (float) ($validated['current_amount'] ?? 0);
        $targetAmount = (float) $validated['target_amount'];

        if ($targetDate) {
            $remainingAmount = max(0, $targetAmount - $currentAmount);
            $periods = max(1, $this->periodCount($validated['cadence'], $startDate, $targetDate));
            $requiredPerPeriod = $remainingAmount / $periods;

            $monthlyIncomeAvg = (float) Transaction::query()
                ->where('type', 'income')
                ->whereNull('transfer_group')
                ->where('spent_at', '>=', now()->subMonths(6)->startOfMonth()->toDateString())
                ->avg('amount');

            $affordableCap = max(100, $monthlyIncomeAvg * 0.35);

            if ($requiredPerPeriod > $affordableCap) {
                throw ValidationException::withMessages([
                    'target_amount' => 'Objectif trop agressif pour ta situation. Reduis le montant ou allonge la periode.',
                ]);
            }
        }

        Goal::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'cadence' => $validated['cadence'],
            'target_amount' => $targetAmount,
            'current_amount' => $currentAmount,
            'start_date' => $startDate->toDateString(),
            'target_date' => $targetDate?->toDateString(),
            'is_archived' => false,
        ]);

        return $this->redirectWithNotification($request, 'Objectif ajoute.');
    }

    public function updateGoal(Request $request, Goal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:saving,investment'],
            'cadence' => ['required', 'in:monthly,quarterly,semiannual,annual'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->toDateString());
        $targetDate = isset($validated['target_date']) ? Carbon::parse($validated['target_date']) : null;

        if ($targetDate && $targetDate->lessThan($startDate)) {
            throw ValidationException::withMessages([
                'target_date' => 'La date cible doit etre apres la date de depart.',
            ]);
        }

        $goal->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'cadence' => $validated['cadence'],
            'target_amount' => (float) $validated['target_amount'],
            'current_amount' => (float) ($validated['current_amount'] ?? 0),
            'start_date' => $startDate->toDateString(),
            'target_date' => $targetDate?->toDateString(),
        ]);

        return $this->redirectWithNotification($request, 'Objectif modifie.', 'success');
    }

    public function destroyGoal(Request $request, Goal $goal): RedirectResponse
    {
        $goal->delete();

        return $this->redirectWithNotification($request, 'Objectif supprime.', 'warning');
    }

    public function archiveGoal(Request $request, Goal $goal): RedirectResponse
    {
        $goal->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        return $this->redirectWithNotification($request, 'Objectif archive.', 'info', false);
    }

    public function upsertAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['nullable', 'exists:accounts,id'],
            'name' => ['nullable', 'string', 'max:120'],
            'institution' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:checking,savings,investment,business'],
            'currency' => ['nullable', 'string', 'size:3'],
            'current_balance' => ['required', 'numeric', 'min:0'],
            'ceiling_amount' => ['nullable', 'numeric', 'min:0'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        if (! empty($validated['account_id'])) {
            $account = Account::findOrFail($validated['account_id']);
            $account->update([
                'name' => $validated['name'] ?? $account->name,
                'institution' => $validated['institution'] ?? $account->institution,
                'type' => $validated['type'] ?? $account->type,
                'currency' => strtoupper($validated['currency'] ?? $account->currency ?? 'EUR'),
                'current_balance' => $validated['current_balance'],
                'ceiling_amount' => $validated['ceiling_amount'] ?: null,
                'target_amount' => $validated['target_amount'] ?: null,
            ]);

            return $this->redirectWithNotification($request, 'Compte mis a jour.', 'success');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:checking,savings,investment,business'],
        ]);

        Account::create([
            'name' => $validated['name'],
            'institution' => $validated['institution'] ?? null,
            'type' => $validated['type'],
            'currency' => strtoupper($validated['currency'] ?? 'EUR'),
            'current_balance' => $validated['current_balance'],
            'ceiling_amount' => $validated['ceiling_amount'] ?: null,
            'target_amount' => $validated['target_amount'] ?: null,
            'is_active' => true,
        ]);

        return $this->redirectWithNotification($request, 'Compte ajoute.', 'success');
    }

    public function destroyAccount(Request $request, Account $account): RedirectResponse
    {
        $account->delete();

        return $this->redirectWithNotification($request, 'Compte supprime.', 'warning');
    }

    private function redirectWithNotification(Request $request, string $message, string $level = 'success', bool $system = true): RedirectResponse
    {
        $fallback = route('overview');
        $target = (string) $request->input('redirect_to', '');

        $redirect = ($target === '' || ! str_starts_with($target, url('/')))
            ? redirect()->to($fallback)
            : redirect()->to($target);

        return $redirect
            ->with('status', $message)
            ->with('fp_notification', [
                'message' => $message,
                'level' => $level,
                'system' => $system,
            ]);
    }

    private function buildViewData(Request $request): array
    {
        $now = Carbon::now();
        $selectedYear = (int) ($request->query('year', $now->year));
        $selectedMonth = (int) ($request->query('month', $now->month));
        $selectedStart = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $selectedEnd = $selectedStart->copy()->endOfMonth();

        if (! $this->financeTablesReady()) {
            return $this->emptyViewData($selectedStart);
        }

        $this->ensureDefaultCategories();
        $this->ensureDefaultAccounts();

        $monthTransactions = Transaction::query()
            ->whereBetween('spent_at', [$selectedStart, $selectedEnd])
            ->whereNull('transfer_group');

        $monthlyIncome = (float) (clone $monthTransactions)->where('type', 'income')->sum('amount');
        $monthlyExpenses = (float) (clone $monthTransactions)->where('type', 'expense')->sum('amount');
        $monthlySavings = max(0, $monthlyIncome - $monthlyExpenses);
        $savingsRate = $monthlyIncome > 0 ? round(($monthlySavings / $monthlyIncome) * 100, 1) : 0;

        $fixedCategories = ['Logement', 'Abonnements', 'Transport'];
        $fixedCharges = (float) Transaction::query()
            ->whereBetween('spent_at', [$selectedStart, $selectedEnd])
            ->where('type', 'expense')
            ->whereNull('transfer_group')
            ->whereHas('category', fn ($q) => $q->whereIn('name', $fixedCategories))
            ->sum('amount');

        $transactions = Transaction::with(['category', 'account'])
            ->latest('spent_at')
            ->limit(250)
            ->get();

        $categories = Category::orderBy('type')->orderBy('name')->get();
        $expenseCategories = $categories->where('type', 'expense')->values();

        $budgets = Budget::with('category')
            ->where('month', $selectedStart->toDateString())
            ->get();

        $spentByCategory = Transaction::selectRaw('category_id, SUM(amount) as total')
            ->where('type', 'expense')
            ->whereNull('transfer_group')
            ->whereBetween('spent_at', [$selectedStart, $selectedEnd])
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $budgetRemaining = 0.0;
        $budgetCompliantCount = 0;
        $budgetOverLimit = [];

        foreach ($budgets as $budget) {
            $spent = (float) ($spentByCategory[$budget->category_id] ?? 0);
            $remaining = (float) $budget->amount - $spent;
            $budgetRemaining += max(0, $remaining);

            if ($spent <= (float) $budget->amount) {
                $budgetCompliantCount++;
            }

            if ((float) $budget->amount > 0 && $spent > ((float) $budget->amount * 1.10)) {
                $budgetOverLimit[] = $budget->category->name;
            }
        }

        $resteAVivre = round($monthlyIncome - $fixedCharges - $budgetRemaining, 2);

        $budgetComplianceRate = $budgets->count() > 0
            ? round(($budgetCompliantCount / $budgets->count()) * 100, 1)
            : 100.0;

        $score = $this->financialScore($savingsRate, $budgetComplianceRate);

        $goals = Goal::query()
            ->where('is_archived', false)
            ->orderBy('target_date')
            ->get()
            ->map(function (Goal $goal): Goal {
                $goal->required_per_period = $this->requiredPerPeriod($goal);
                return $goal;
            });

        $archivedGoalsCount = Goal::query()->where('is_archived', true)->count();

        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $projectionDate = now()->addDays(30)->endOfDay();
        $projectionRows = [];

        foreach ($accounts as $account) {
            $futureDelta = (float) Transaction::query()
                ->where('account_id', $account->id)
                ->whereNotNull('split_group')
                ->whereBetween('spent_at', [now()->toDateString(), $projectionDate->toDateString()])
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as delta")
                ->value('delta');

            $projectionRows[$account->id] = [
                'delta_30' => round($futureDelta, 2),
                'projected_balance_30' => round((float) $account->current_balance + $futureDelta, 2),
            ];
        }

        $currentTotal = (float) $accounts->sum('current_balance');
        $projectedTotal = (float) collect($projectionRows)->sum('projected_balance_30');
        $projectionJ30 = [
            'current' => round($currentTotal, 2),
            'projected' => round($projectedTotal, 2),
            'delta' => round($projectedTotal - $currentTotal, 2),
            'trend' => $projectedTotal >= $currentTotal ? 'up' : 'down',
        ];

        $scoreSante = [
            'grade' => $score['grade'],
            'label' => $score['label'],
            'points' => $score['points'],
        ];

        $recommendations = $this->buildRecommendations(
            $accounts,
            $monthlyIncome,
            $monthlyExpenses,
            $goals,
            $resteAVivre,
            $budgetOverLimit
        );

        $insightNotifications = $this->buildInsightNotifications($resteAVivre, $budgetOverLimit, $recommendations);

        return [
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpenses' => $monthlyExpenses,
            'monthlySavings' => $monthlySavings,
            'savingsRate' => $savingsRate,
            'fixedCharges' => $fixedCharges,
            'budgetRemaining' => round($budgetRemaining, 2),
            'resteAVivre' => $resteAVivre,
            'score' => $score,
            'scoreSante' => $scoreSante,
            'transactions' => $transactions,
            'categories' => $categories,
            'expenseCategories' => $expenseCategories,
            'budgets' => $budgets,
            'spentByCategory' => $spentByCategory,
            'budgetOverLimit' => $budgetOverLimit,
            'goals' => $goals,
            'archivedGoalsCount' => $archivedGoalsCount,
            'accounts' => $accounts,
            'projectionRows' => $projectionRows,
            'projectionJ30' => $projectionJ30,
            'recommendations' => $recommendations,
            'insightNotifications' => $insightNotifications,
            'monthLabel' => $selectedStart->translatedFormat('F Y'),
            'monthValue' => $selectedStart->format('Y-m'),
            'today' => $now->toDateString(),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'availableYears' => range($now->year - 2, $now->year + 1),
        ];
    }

    private function financialScore(float $savingsRate, float $budgetCompliance): array
    {
        $points = min(100, max(0, ($savingsRate * 0.6) + ($budgetCompliance * 0.4)));

        if ($points >= 80) {
            return ['grade' => 'A', 'label' => 'Excellent', 'points' => round($points, 1)];
        }

        if ($points >= 65) {
            return ['grade' => 'B', 'label' => 'Solide', 'points' => round($points, 1)];
        }

        if ($points >= 50) {
            return ['grade' => 'C', 'label' => 'A surveiller', 'points' => round($points, 1)];
        }

        return ['grade' => 'D', 'label' => 'Fragile', 'points' => round($points, 1)];
    }

    private function ensureDefaultCategories(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        Category::insert([
            ['name' => 'Salaire', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Transfert entrant', 'type' => 'income'],
            ['name' => 'Alimentation', 'type' => 'expense'],
            ['name' => 'Transport', 'type' => 'expense'],
            ['name' => 'Logement', 'type' => 'expense'],
            ['name' => 'Abonnements', 'type' => 'expense'],
            ['name' => 'Loisirs', 'type' => 'expense'],
            ['name' => 'Epargne', 'type' => 'expense'],
            ['name' => 'Investissement', 'type' => 'expense'],
            ['name' => 'Transfert sortant', 'type' => 'expense'],
        ]);
    }

    private function ensureDefaultAccounts(): void
    {
        if (Account::query()->exists()) {
            return;
        }

        foreach ([
            ['name' => 'Livret A', 'institution' => 'La Banque Postale', 'type' => 'savings', 'current_balance' => 360, 'ceiling_amount' => 22950, 'target_amount' => 5000],
            ['name' => 'LDDS', 'institution' => 'Credit Agricole', 'type' => 'savings', 'current_balance' => 10, 'ceiling_amount' => 12000, 'target_amount' => 2000],
            ['name' => 'Livret Jeune', 'institution' => 'Credit Agricole', 'type' => 'savings', 'current_balance' => 900, 'ceiling_amount' => 1600, 'target_amount' => 1600],
            ['name' => 'Compte courant alternance', 'institution' => 'Banque principale', 'type' => 'checking', 'current_balance' => 0, 'ceiling_amount' => null, 'target_amount' => 1000],
            ['name' => 'Meria EI NJIEZM.FR', 'institution' => 'Freelance', 'type' => 'business', 'current_balance' => 280, 'ceiling_amount' => null, 'target_amount' => 4000],
            ['name' => 'Revolut perso', 'institution' => 'Revolut', 'type' => 'checking', 'current_balance' => 0, 'ceiling_amount' => null, 'target_amount' => 500],
            ['name' => 'Revolut Robo Advisor', 'institution' => 'Revolut', 'type' => 'investment', 'current_balance' => 9, 'ceiling_amount' => null, 'target_amount' => 1000],
            ['name' => 'SumUp freelance', 'institution' => 'SumUp', 'type' => 'business', 'current_balance' => 0, 'ceiling_amount' => null, 'target_amount' => 1000],
        ] as $row) {
            Account::create($row);
        }
    }

    private function financeTablesReady(): bool
    {
        return Schema::hasTable('categories')
            && Schema::hasTable('transactions')
            && Schema::hasTable('budgets')
            && Schema::hasTable('goals')
            && Schema::hasTable('accounts');
    }

    private function emptyViewData(Carbon $monthStart): array
    {
        return [
            'monthlyIncome' => 0,
            'monthlyExpenses' => 0,
            'monthlySavings' => 0,
            'savingsRate' => 0,
            'fixedCharges' => 0,
            'budgetRemaining' => 0,
            'resteAVivre' => 0,
            'score' => ['grade' => 'N/A', 'label' => 'Non calcule', 'points' => 0],
            'scoreSante' => ['grade' => 'N/A', 'label' => 'Non calcule', 'points' => 0],
            'transactions' => collect(),
            'categories' => collect(),
            'expenseCategories' => collect(),
            'budgets' => collect(),
            'spentByCategory' => collect(),
            'budgetOverLimit' => [],
            'goals' => collect(),
            'archivedGoalsCount' => 0,
            'accounts' => collect(),
            'projectionRows' => [],
            'projectionJ30' => ['current' => 0, 'projected' => 0, 'delta' => 0, 'trend' => 'up'],
            'recommendations' => [],
            'insightNotifications' => [],
            'monthLabel' => $monthStart->translatedFormat('F Y'),
            'monthValue' => $monthStart->format('Y-m'),
            'today' => now()->toDateString(),
            'selectedMonth' => (int) $monthStart->format('m'),
            'selectedYear' => (int) $monthStart->format('Y'),
            'availableYears' => range(now()->year - 2, now()->year + 1),
        ];
    }

    private function buildRecommendations(Collection $accounts, float $monthlyIncome, float $monthlyExpenses, Collection $goals, float $resteAVivre, array $budgetOverLimit): array
    {
        $recommendations = [];

        $livretJeune = $accounts->firstWhere('name', 'Livret Jeune');
        if ($livretJeune) {
            $remaining = max(0, (float) ($livretJeune->ceiling_amount ?? 0) - (float) $livretJeune->current_balance);
            $recommendations[] = 'Priorite 1: completer le Livret Jeune (reste env. '.number_format($remaining, 0, ',', ' ').' EUR).';
        }

        $freelance = $accounts->firstWhere('name', 'Meria EI NJIEZM.FR');
        if ($freelance) {
            $left = max(0, (float) ($freelance->target_amount ?? 4000) - (float) $freelance->current_balance);
            $recommendations[] = 'Priorite 2: atteindre 4 000 EUR sur le compte freelance avant versement salaire (reste '.number_format($left, 0, ',', ' ').' EUR).';
        }

        if ($monthlyIncome > 0) {
            $needs = round($monthlyIncome * 0.55, 2);
            $wants = round($monthlyIncome * 0.25, 2);
            $saving = round($monthlyIncome * 0.20, 2);
            $recommendations[] = 'Cadre mensuel: besoins <= '.number_format($needs, 0, ',', ' ').' EUR, envies <= '.number_format($wants, 0, ',', ' ').' EUR, epargne >= '.number_format($saving, 0, ',', ' ').' EUR.';
        }

        if ($resteAVivre < 0) {
            $recommendations[] = 'Alerte cashflow: reste a vivre negatif. Ajuster budgets loisirs/abonnements immediatement.';
        }

        if (! empty($budgetOverLimit)) {
            $recommendations[] = 'Budget depasse >10% sur: '.implode(', ', $budgetOverLimit).'.';
        }

        if ($goals->isNotEmpty()) {
            $hardGoal = $goals->sortByDesc('required_per_period')->first();
            if ($hardGoal && $hardGoal->required_per_period > 0) {
                $recommendations[] = 'Objectif le plus exigeant: '.$hardGoal->title.' ('.number_format($hardGoal->required_per_period, 2, ',', ' ').' EUR / periode).';
            }
        }

        $recommendations[] = 'Freelance: offres efficaces a vendre: site vitrine express (490-790 EUR), maintenance mensuelle (59-149 EUR/mois), automatisation simple (250-600 EUR).';
        $recommendations[] = 'Leboncoin: cible 100-250 EUR/mois de ventes materiel inutilise pour accelerer epargne ou fonds business.';

        return $recommendations;
    }

    private function buildInsightNotifications(float $resteAVivre, array $budgetOverLimit, array $recommendations): array
    {
        $notifications = [];

        if ($resteAVivre < 0) {
            $notifications[] = [
                'message' => 'Alerte: reste a vivre negatif ce mois-ci.',
                'level' => 'error',
                'system' => true,
            ];
        }

        if (! empty($budgetOverLimit)) {
            $notifications[] = [
                'message' => 'Budget depasse >110%: '.implode(', ', $budgetOverLimit).'.',
                'level' => 'warning',
                'system' => true,
            ];
        }

        if (! empty($recommendations)) {
            $notifications[] = [
                'message' => 'Reco: '.$recommendations[0],
                'level' => 'info',
                'system' => false,
            ];
        }

        return array_slice($notifications, 0, 3);
    }
    private function requiredPerPeriod(Goal $goal): float
    {
        if (! $goal->target_date) {
            return max(0, (float) $goal->target_amount - (float) $goal->current_amount);
        }

        $start = $goal->start_date ? Carbon::parse($goal->start_date) : now();
        $periods = max(1, $this->periodCount($goal->cadence ?? 'monthly', $start, Carbon::parse($goal->target_date)));

        return round(max(0, (float) $goal->target_amount - (float) $goal->current_amount) / $periods, 2);
    }

    private function periodCount(string $cadence, Carbon $start, Carbon $target): int
    {
        $months = max(1, $start->copy()->startOfMonth()->diffInMonths($target->copy()->startOfMonth()) + 1);

        return match ($cadence) {
            'monthly' => $months,
            'quarterly' => (int) ceil($months / 3),
            'semiannual' => (int) ceil($months / 6),
            'annual' => (int) ceil($months / 12),
            default => $months,
        };
    }
}

