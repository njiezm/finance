@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="fp-glass p-6 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Capacite de depense</span>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-4xl font-bold num mt-1">{{ number_format($resteAVivre,2,',',' ') }}</h2>
                    <span class="text-xl font-medium text-slate-400">EUR</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Charges fixes securisees pour ce mois.</p>
            </div>
            @php
                $gradeBg = match($scoreSante['grade'] ?? 'D') {
                    'A' => 'bg-emerald-500 shadow-emerald-500/30',
                    'B' => 'bg-sky-500 shadow-sky-500/30',
                    'C' => 'bg-amber-500 shadow-amber-500/30',
                    default => 'bg-rose-500 shadow-rose-500/30',
                };
            @endphp
            <div class="flex items-center gap-4 bg-white/50 dark:bg-slate-900/50 p-3 rounded-3xl border border-white/20">
                <div class="w-14 h-14 rounded-2xl {{ $gradeBg }} shadow-lg flex items-center justify-center text-white text-2xl font-bold">{{ $scoreSante['grade'] ?? 'D' }}</div>
                <div>
                    <div class="text-xs font-bold uppercase text-slate-400">Sante Financiere</div>
                    <div class="text-sm font-semibold">{{ $scoreSante['label'] ?? 'N/A' }} ({{ number_format(($scoreSante['points'] ?? 0) * 10, 0, ',', ' ') }} pts)</div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
            <div class="space-y-1"><div class="text-xs text-slate-400 uppercase">Revenus</div><div class="num font-bold text-emerald-500">+{{ number_format($monthlyIncome,2,',',' ') }}</div></div>
            <div class="space-y-1"><div class="text-xs text-slate-400 uppercase">Fixes</div><div class="num font-bold text-slate-600 dark:text-slate-300">-{{ number_format($fixedCharges,2,',',' ') }}</div></div>
            <div class="space-y-1"><div class="text-xs text-slate-400 uppercase">Budgets</div><div class="num font-bold text-blue-500">-{{ number_format($budgetRemaining,2,',',' ') }}</div></div>
            <div class="space-y-1"><div class="text-xs text-slate-400 uppercase">Proj. J+30</div><div class="num font-bold text-indigo-500">{{ number_format($projectionJ30['projected'] ?? 0,2,',',' ') }}</div></div>
        </div>
    </section>

    <section>
        <div class="flex justify-between items-end mb-4 px-2">
            <h3 class="font-bold">Mes Comptes</h3>
            <a href="{{ route('accounts.page') }}" class="text-sm text-blue-600 font-medium">Voir tout</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($accounts->take(2) as $account)
                @php
                    $gradient = match($account->type) {
                        'savings' => 'from-blue-700 via-indigo-700 to-slate-900',
                        'investment' => 'from-emerald-700 via-teal-700 to-slate-900',
                        'business' => 'from-violet-700 via-fuchsia-700 to-slate-900',
                        default => 'from-slate-800 via-slate-700 to-slate-900',
                    };
                @endphp
                <div class="v-card bg-gradient-to-br {{ $gradient }} p-6 text-white flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="chip"></div>
                        <div class="text-right">
                            <div class="text-xs opacity-60 uppercase">{{ strtoupper($account->type) }}</div>
                            <div class="font-bold">{{ $account->name }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs opacity-50 mb-1">SOLDE DISPONIBLE</div>
                        <div class="text-3xl font-bold num tracking-tight">{{ number_format((float)$account->current_balance,2,',',' ') }} EUR</div>
                    </div>
                    <div class="flex justify-between items-center text-xs opacity-60">
                        <div class="tracking-[0.2em]">**** **** **** {{ str_pad((string)$account->id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div class="bg-white/20 px-2 py-1 rounded">ACTIF</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid grid-cols-4 gap-4">
        <a href="{{ route('transactions.page') }}" class="flex flex-col items-center gap-2 group transition-all text-decoration-none text-current">
            <div class="w-16 h-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white group-hover:scale-110"><i data-lucide="send"></i></div>
            <span class="text-xs font-semibold">Virement</span>
        </a>
        <a href="{{ route('transactions.page') }}" class="flex flex-col items-center gap-2 group transition-all text-decoration-none text-current">
            <div class="w-16 h-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-emerald-600 shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white group-hover:scale-110"><i data-lucide="scan-line"></i></div>
            <span class="text-xs font-semibold">Scanner</span>
        </a>
        <a href="{{ route('goals.page') }}" class="flex flex-col items-center gap-2 group transition-all text-decoration-none text-current">
            <div class="w-16 h-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-indigo-600 shadow-sm transition-all group-hover:bg-indigo-600 group-hover:text-white group-hover:scale-110"><i data-lucide="target"></i></div>
            <span class="text-xs font-semibold">Objectif</span>
        </a>
        <a href="{{ route('recommendations.page') }}" class="flex flex-col items-center gap-2 group transition-all text-decoration-none text-current">
            <div class="w-16 h-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-amber-500 shadow-sm transition-all group-hover:bg-amber-500 group-hover:text-white group-hover:scale-110"><i data-lucide="sparkles"></i></div>
            <span class="text-xs font-semibold">IA Reco</span>
        </a>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-24">
        <div class="fp-glass p-5">
            <h4 class="text-sm font-bold mb-4 flex items-center gap-2"><i data-lucide="target" class="w-4 h-4 text-blue-500"></i> Objectifs Actifs</h4>
            <div class="space-y-4">
                @forelse($goals->take(2) as $goal)
                    @php $progress = $goal->target_amount > 0 ? min(100, round(((float)$goal->current_amount / (float)$goal->target_amount) * 100)) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1 font-medium"><span>{{ $goal->title }}</span><span class="num">{{ $progress }}%</span></div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden"><div class="bg-blue-600 h-full rounded-full" style="width: {{ $progress }}%"></div></div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 dark:text-slate-400">Aucun objectif actif.</p>
                @endforelse
            </div>
        </div>

        <div class="fp-glass p-5 border-rose-200 dark:border-rose-900/30">
            <h4 class="text-sm font-bold mb-4 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500"></i> Alertes Budget</h4>
            @if(!empty($budgetOverLimit))
                @foreach($budgetOverLimit as $name)
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 rounded-2xl animate-pulse mb-2">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-rose-700 dark:text-rose-400">{{ $name }}</span>
                            <span class="text-[10px] bg-rose-200 text-rose-800 px-2 py-0.5 rounded-full">Depassement</span>
                        </div>
                        <div class="text-xs mt-1">Depense superieure a 110% du budget.</div>
                    </div>
                @endforeach
            @else
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl">
                    <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Aucune alerte budget actuellement.</div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection