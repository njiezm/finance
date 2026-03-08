@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="text-xl font-bold">Transactions</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="openScan()">Scanner</button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#txAddModal">Ajouter</button>
        </div>
    </div>

    <div class="fp-card p-4">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold">Historique recent</h3>
            <div class="flex gap-2">
                <select id="filter-month" class="form-select form-select-sm rounded-xl"><option value="">Mois</option></select>
                <select id="filter-year" class="form-select form-select-sm rounded-xl"><option value="">Annee</option></select>
            </div>
        </div>
        <div id="tx-list" class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($transactions as $transaction)
                @php
                    $catName = strtolower($transaction->category?->name ?? '');
                    $badgeClass = str_contains($catName, 'loisir')
                        ? 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300'
                        : (str_contains($catName, 'aliment')
                            ? 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
                            : ((str_contains($catName, 'logement') || str_contains($catName, 'abonnement') || str_contains($catName, 'transport'))
                                ? 'bg-sky-50 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200'));
                @endphp
                <div class="py-3 flex justify-between items-start gap-3" data-date="{{ $transaction->spent_at->format('Y-m-d') }}">
                    <div>
                        <div class="font-semibold text-sm">{{ $transaction->label }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $transaction->spent_at->format('d/m/Y') }} &middot; {{ $transaction->account?->name ?? '-' }}</div>
                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold mt-1 {{ $badgeClass }}">{{ $transaction->category?->name ?? '-' }}</span>
                    </div>
                    <div class="text-right">
                        <div class="num font-bold {{ $transaction->type === 'income' ? 'text-emerald-500' : 'text-rose-500' }}">{{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount,2,',',' ') }}</div>
                        @if($transaction->split_total)
                            <div class="text-[11px] text-slate-400">{{ $transaction->split_number }}/{{ $transaction->split_total }}</div>
                        @endif
                        <div class="mt-2 d-flex gap-1 justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 tx-edit-btn"
                                data-tx-id="{{ $transaction->id }}"
                                data-tx-label="{{ $transaction->label }}"
                                data-tx-amount="{{ $transaction->amount }}"
                                data-tx-type="{{ $transaction->type }}"
                                data-tx-category="{{ $transaction->category_id }}"
                                data-tx-account="{{ $transaction->account_id }}"
                                data-tx-date="{{ $transaction->spent_at->format('Y-m-d') }}"
                                data-tx-notes="{{ $transaction->notes }}">Modifier</button>
                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
                                @if($transaction->split_total)
                                    <input type="hidden" name="delete_group" value="1">
                                @endif
                                <button class="btn btn-sm btn-outline-danger py-0 px-2">Suppr.</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-4 text-sm text-slate-500 dark:text-slate-400">Aucune transaction.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="txAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-3">Nouvelle transaction</h5>
        <form method="POST" action="{{ route('transactions.store') }}" class="row g-2">
          @csrf
          <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
          <div class="col-12"><input type="text" name="label" class="form-control" placeholder="Libelle" required></div>
          <div class="col-6"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Montant" required></div>
          <div class="col-6"><select name="type" class="form-select"><option value="expense">Depense</option><option value="income">Revenu</option></select></div>
          <div class="col-6"><select name="account_id" class="form-select"><option value="">Selectionner compte</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
          <div class="col-6"><select name="category_id" class="form-select" required>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type }})</option>@endforeach</select></div>
          <div class="col-6"><input type="date" name="spent_at" value="{{ $today }}" class="form-control" required></div>
          <div class="col-6"><input type="number" min="1" max="24" name="installments" value="1" class="form-control" placeholder="Fractionnement"></div>
          <div class="col-12"><input type="text" name="notes" class="form-control" placeholder="Notes"></div>
          <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Ajouter</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="txEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-3">Modifier transaction</h5>
        <form method="POST" id="txEditForm" class="row g-2">
          @csrf
          @method('PATCH')
          <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
          <div class="col-12"><input id="edit-label" class="form-control" name="label" required></div>
          <div class="col-6"><input id="edit-amount" class="form-control" name="amount" type="number" step="0.01" required></div>
          <div class="col-6"><input id="edit-date" class="form-control" name="spent_at" type="date" required></div>
          <div class="col-6"><select id="edit-type" class="form-select" name="type" required><option value="expense">Depense</option><option value="income">Revenu</option></select></div>
          <div class="col-6"><select id="edit-account" class="form-select" name="account_id"><option value="">Selectionner</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
          <div class="col-12"><select id="edit-category" class="form-select" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type }})</option>@endforeach</select></div>
          <div class="col-12"><input id="edit-notes" class="form-control" name="notes" placeholder="Notes"></div>
          <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="scan-modal" class="fixed inset-0 z-[2000] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeScan()"></div>
    <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-[32px] overflow-hidden shadow-2xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold">Scanner un ticket</h3>
                <button onclick="closeScan()" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 border-0 bg-transparent"><i data-lucide="x"></i></button>
            </div>
            <div class="aspect-[3/4] bg-slate-100 dark:bg-slate-800 rounded-2xl relative overflow-hidden flex items-center justify-center">
                <div id="camera-overlay" class="absolute inset-0 border-2 border-blue-500/50 m-8 rounded-lg pointer-events-none">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-500"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-500"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-500"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-500"></div>
                    <div class="absolute inset-x-0 top-1/2 h-0.5 bg-blue-500/30 animate-pulse"></div>
                </div>
                <div class="text-center p-6" id="capture-ui">
                    <i data-lucide="scan" class="w-12 h-12 mx-auto mb-4 text-blue-500"></i>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Alignez le ticket dans le cadre</p>
                    <button onclick="simulateScan()" class="mt-6 px-8 py-3 bg-blue-600 text-white rounded-full font-bold shadow-lg shadow-blue-600/30 border-0">Prendre la photo</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const list = document.getElementById('tx-list');
  const monthSelect = document.getElementById('filter-month');
  const yearSelect = document.getElementById('filter-year');

  if (list && monthSelect && yearSelect) {
    const rows = Array.from(list.querySelectorAll('[data-date]'));
    const months = new Set();
    const years = new Set();

    rows.forEach((row) => {
      const [y, m] = row.dataset.date.split('-');
      months.add(m);
      years.add(y);
    });

    [...months].sort().forEach((m) => {
      const o = document.createElement('option');
      o.value = m;
      o.textContent = m;
      monthSelect.appendChild(o);
    });

    [...years].sort().reverse().forEach((y) => {
      const o = document.createElement('option');
      o.value = y;
      o.textContent = y;
      yearSelect.appendChild(o);
    });

    const applyFilter = () => {
      const month = monthSelect.value;
      const year = yearSelect.value;
      rows.forEach((row) => {
        const [y, m] = row.dataset.date.split('-');
        row.style.display = (!month || month === m) && (!year || year === y) ? '' : 'none';
      });
    };

    monthSelect.addEventListener('change', applyFilter);
    yearSelect.addEventListener('change', applyFilter);
  }

  const txEditModalEl = document.getElementById('txEditModal');
  if (txEditModalEl) {
    const txEditModal = new bootstrap.Modal(txEditModalEl);
    const txEditForm = document.getElementById('txEditForm');

    document.querySelectorAll('.tx-edit-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const txId = btn.dataset.txId;
        if (!txId || !txEditForm) return;

        txEditForm.action = `/transactions/${txId}`;
        document.getElementById('edit-label').value = btn.dataset.txLabel || '';
        document.getElementById('edit-amount').value = btn.dataset.txAmount || '';
        document.getElementById('edit-date').value = btn.dataset.txDate || '';
        document.getElementById('edit-type').value = btn.dataset.txType || 'expense';
        document.getElementById('edit-category').value = btn.dataset.txCategory || '';
        document.getElementById('edit-account').value = btn.dataset.txAccount || '';
        document.getElementById('edit-notes').value = btn.dataset.txNotes || '';
        txEditModal.show();
      });
    });
  }
})();

function openScan() {
  document.getElementById('scan-modal')?.classList.remove('hidden');
}

function closeScan() {
  document.getElementById('scan-modal')?.classList.add('hidden');
}

function simulateScan() {
  const ui = document.getElementById('capture-ui');
  if (!ui) return;

  ui.innerHTML = `
    <div class="loading-shimmer w-full h-full absolute inset-0 opacity-20"></div>
    <div class="flex flex-col items-center">
      <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
      <p class="text-sm font-bold text-blue-500">Extraction IA en cours...</p>
    </div>
  `;

  setTimeout(() => {
    closeScan();

    const labelInput = document.querySelector('#txAddModal input[name="label"]');
    const amountInput = document.querySelector('#txAddModal input[name="amount"]');
    if (labelInput) labelInput.value = 'Carrefour Market';
    if (amountInput) amountInput.value = '42.85';

    const addModalEl = document.getElementById('txAddModal');
    if (addModalEl) new bootstrap.Modal(addModalEl).show();

    if (window.fpNotify) window.fpNotify('Ticket scanne et champs pre-remplis', 'success');

    ui.innerHTML = `
      <i data-lucide="scan" class="w-12 h-12 mx-auto mb-4 text-blue-500"></i>
      <p class="text-sm text-slate-500 dark:text-slate-400">Alignez le ticket dans le cadre</p>
      <button onclick="simulateScan()" class="mt-6 px-8 py-3 bg-blue-600 text-white rounded-full font-bold shadow-lg shadow-blue-600/30 border-0">Prendre la photo</button>
    `;

    if (window.lucide) window.lucide.createIcons();
  }, 1000);
}
</script>
@endpush
