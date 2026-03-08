@extends('layouts.app')

@section('content')
<section id="top" class="fp-section mb-4">
    <div class="fp-card card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <h1 class="h3 mb-1">Pilotage finances perso</h1>
                    <p class="text-secondary mb-0">Vue mobile simplifiee pour organiser, budgetiser, epargner et investir.</p>
                </div>
                <div class="small text-secondary">Periode: {{ $monthLabel }}</div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-3" style="background:#ecfeff;">
                        <div class="text-secondary small">Revenus</div>
                        <div class="fw-bold">{{ number_format($monthlyIncome, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-3" style="background:#fef2f2;">
                        <div class="text-secondary small">Depenses</div>
                        <div class="fw-bold">{{ number_format($monthlyExpenses, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-3" style="background:#f0fdf4;">
                        <div class="text-secondary small">Epargne</div>
                        <div class="fw-bold">{{ number_format($monthlySavings, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-3" style="background:#eff6ff;">
                        <div class="text-secondary small">Taux epargne</div>
                        <div class="fw-bold">{{ $savingsRate }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="accounts" class="fp-section mb-4">
    <div class="fp-card card">
        <div class="card-header">Comptes et plafonds</div>
        <div class="card-body">
            <div class="row g-3">
                @forelse ($accounts as $account)
                    @php
                        $ceiling = (float) ($account->ceiling_amount ?? 0);
                        $target = (float) ($account->target_amount ?? 0);
                        $balance = (float) $account->current_balance;
                        $ceilingProgress = $ceiling > 0 ? min(100, round(($balance / $ceiling) * 100)) : null;
                        $targetProgress = $target > 0 ? min(100, round(($balance / $target) * 100)) : null;
                    @endphp
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                <div>
                                    <div class="fw-semibold">{{ $account->name }}</div>
                                    <div class="small text-secondary">{{ $account->institution }} - {{ $account->type }}</div>
                                </div>
                                <span class="badge text-bg-light">{{ number_format($balance, 2, ',', ' ') }} EUR</span>
                            </div>

                            @if (!is_null($ceilingProgress))
                                <div class="small mt-2">Plafond: {{ number_format($ceiling, 0, ',', ' ') }} EUR ({{ $ceilingProgress }}%)</div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $ceilingProgress }}%"></div>
                                </div>
                            @endif

                            @if (!is_null($targetProgress))
                                <div class="small mt-2">Objectif: {{ number_format($target, 0, ',', ' ') }} EUR ({{ $targetProgress }}%)</div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $targetProgress }}%"></div>
                                </div>
                            @endif

                            <form class="row g-2 mt-2" method="POST" action="{{ route('accounts.upsert') }}">
                                @csrf
                                <input type="hidden" name="account_id" value="{{ $account->id }}">
                                <div class="col-4">
                                    <input class="form-control form-control-sm" name="current_balance" type="number" step="0.01" value="{{ $account->current_balance }}" placeholder="Solde" required>
                                </div>
                                <div class="col-4">
                                    <input class="form-control form-control-sm" name="ceiling_amount" type="number" step="0.01" value="{{ $account->ceiling_amount }}" placeholder="Plafond">
                                </div>
                                <div class="col-4">
                                    <input class="form-control form-control-sm" name="target_amount" type="number" step="0.01" value="{{ $account->target_amount }}" placeholder="Objectif">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-outline-dark">Mettre a jour</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-secondary">Aucun compte configure.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section id="transactions" class="fp-section mb-4">
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="fp-card card h-100">
                <div class="card-header">Saisie transaction (inclut paiement fractionne)</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.store') }}" class="row g-3">
                        @csrf
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="expense">Depense</option>
                                <option value="income">Revenu</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Compte</label>
                            <select class="form-select" name="account_id">
                                <option value="">Aucun</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Categorie</label>
                            <select class="form-select" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Libelle</label>
                            <input type="text" class="form-control" name="label" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Montant total</label>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Date depart</label>
                            <input type="date" class="form-control" name="spent_at" value="{{ $today }}" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Nb paiements</label>
                            <input type="number" min="1" max="24" class="form-control" name="installments" value="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="fp-card card h-100">
                <div class="card-header">Dernieres lignes</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Libelle</th>
                                <th>Compte</th>
                                <th class="text-end">Montant</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->spent_at->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $transaction->label }}
                                        @if($transaction->split_total)
                                            <div class="small text-secondary">Fraction {{ $transaction->split_number }}/{{ $transaction->split_total }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->account?->name ?? '-' }}</td>
                                    <td class="text-end {{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2, ',', ' ') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">Aucune transaction.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="goals" class="fp-section mb-4">
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="fp-card card h-100">
                <div class="card-header">Budgets mensuels depenses</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('budgets.store') }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-sm-5">
                            <label class="form-label">Categorie</label>
                            <select class="form-select" name="category_id" required>
                                @foreach ($expenseCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Mois</label>
                            <input type="month" class="form-control" name="month" value="{{ $monthValue }}" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Montant</label>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary">Sauvegarder budget</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Categorie</th><th>Budget</th><th>Depense</th><th>Reste</th></tr></thead>
                            <tbody>
                            @forelse ($budgets as $budget)
                                @php
                                    $spent = (float) ($spentByCategory[$budget->category_id] ?? 0);
                                    $remaining = (float) $budget->amount - $spent;
                                @endphp
                                <tr>
                                    <td>{{ $budget->category->name }}</td>
                                    <td>{{ number_format($budget->amount, 2, ',', ' ') }}</td>
                                    <td>{{ number_format($spent, 2, ',', ' ') }}</td>
                                    <td class="{{ $remaining < 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ number_format($remaining, 2, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-secondary">Aucun budget.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="fp-card card h-100">
                <div class="card-header">Objectifs periodiques realistes</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('goals.store') }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-sm-6">
                            <label class="form-label">Titre</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="saving">Epargne</option>
                                <option value="investment">Investissement</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Cadence</label>
                            <select class="form-select" name="cadence" required>
                                <option value="monthly">Mensuel</option>
                                <option value="quarterly">Trimestriel</option>
                                <option value="semiannual">Semestriel</option>
                                <option value="annual">Annuel</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Montant cible</label>
                            <input type="number" step="0.01" class="form-control" name="target_amount" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Montant actuel</label>
                            <input type="number" step="0.01" class="form-control" name="current_amount" value="0">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Date depart</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $today }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Date cible</label>
                            <input type="date" class="form-control" name="target_date">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-dark">Ajouter objectif</button>
                        </div>
                    </form>

                    <ul class="list-group list-group-flush">
                        @forelse ($goals as $goal)
                            @php
                                $remaining = max(0, (float)$goal->target_amount - (float)$goal->current_amount);
                                $progress = $goal->target_amount > 0 ? min(100, round(((float)$goal->current_amount / (float)$goal->target_amount) * 100)) : 0;
                                $cadenceLabel = [
                                    'monthly' => 'mensuel',
                                    'quarterly' => 'trimestriel',
                                    'semiannual' => 'semestriel',
                                    'annual' => 'annuel',
                                ][$goal->cadence] ?? $goal->cadence;
                            @endphp
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between gap-2 align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $goal->title }}</div>
                                        <div class="small text-secondary">{{ $goal->type }} | {{ $cadenceLabel }} | reste {{ number_format($remaining, 2, ',', ' ') }} EUR</div>
                                        <div class="small">A fournir: <strong>{{ number_format((float)$goal->required_per_period, 2, ',', ' ') }} EUR / periode</strong></div>
                                    </div>
                                    <span class="badge text-bg-info">{{ $progress }}%</span>
                                </div>
                                <div class="progress mt-2" style="height: 7px;">
                                    <div class="progress-bar" style="width: {{ $progress }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-secondary">Aucun objectif pour le moment.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="reco" class="fp-section mb-4">
    <div class="fp-card card">
        <div class="card-header">Recommandations personnalisees</div>
        <div class="card-body">
            <div class="row g-3">
                @forelse ($recommendations as $recommendation)
                    <div class="col-12 col-lg-6">
                        <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            {{ $recommendation }}
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-secondary">Ajoute des transactions et comptes pour activer les recommandations.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection