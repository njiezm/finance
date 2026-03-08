@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="fp-card p-6 bg-gradient-to-br from-blue-600 to-indigo-800 text-white border-0">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-white/20 rounded-xl"><i data-lucide="sparkles"></i></div>
            <h2 class="text-lg font-bold">Recommandations IA</h2>
        </div>
        <p class="text-blue-100 text-sm leading-relaxed">Base sur vos transactions des 30 derniers jours, voici vos actions prioritaires.</p>
    </div>

    <div class="space-y-4">
        @forelse($recommendations as $recommendation)
            @php
                $isAlert = str_contains(strtolower($recommendation), 'alerte') || str_contains(strtolower($recommendation), 'reduire');
                $icon = $isAlert ? 'zap' : 'trending-up';
                $color = $isAlert
                    ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600'
                    : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600';
            @endphp
            <div class="fp-card p-4 flex gap-4 items-start">
                <div class="p-3 {{ $color }} rounded-2xl"><i data-lucide="{{ $icon }}"></i></div>
                <div>
                    <h4 class="font-bold text-sm">Suggestion</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $recommendation }}</p>
                </div>
            </div>
        @empty
            <div class="fp-card p-4 text-sm text-slate-500 dark:text-slate-400">Ajoute des donnees pour obtenir des recommandations.</div>
        @endforelse
    </div>
</div>
@endsection