@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="text-xl font-bold">Mes comptes</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accountAddModal">Ajouter</button>
    </div>

    <div class="row g-4">
        @foreach($accounts as $account)
            @php
                $gradient = match($account->type) {
                    'checking' => 'from-slate-800 via-slate-700 to-slate-900',
                    'savings' => 'from-blue-700 via-indigo-700 to-slate-900',
                    'investment' => 'from-emerald-700 via-teal-700 to-slate-900',
                    'business' => 'from-violet-700 via-fuchsia-700 to-slate-900',
                    default => 'from-slate-800 to-slate-900'
                };
                $projection = $projectionRows[$account->id] ?? ['delta_30' => 0, 'projected_balance_30' => (float)$account->current_balance];
            @endphp
            <div class="col-12 col-lg-6">
                <div class="group relative aspect-[1.58/1] rounded-[24px] shadow-2xl overflow-hidden transform-gpu transition-all duration-200 hover:-translate-y-1 hover:rotate-[0.4deg]">
                    <div class="absolute inset-0 bg-gradient-to-br {{ $gradient }}"></div>
                    <div class="absolute inset-0 bg-white/5 backdrop-blur-[1px]"></div>

                    <button type="button"
                        class="account-edit-btn absolute top-3 right-3 z-20 rounded-full border-0 bg-white/20 p-2 text-white hover:bg-white/30"
                        data-account-id="{{ $account->id }}"
                        data-account-name="{{ $account->name }}"
                        data-account-institution="{{ $account->institution }}"
                        data-account-type="{{ $account->type }}"
                        data-account-currency="{{ $account->currency ?? 'EUR' }}"
                        data-account-balance="{{ $account->current_balance }}"
                        data-account-ceiling="{{ $account->ceiling_amount }}"
                        data-account-target="{{ $account->target_amount }}"
                        title="Modifier ce compte">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>

                    <div class="relative h-full p-4 text-white d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start">
                            <svg width="46" height="34" viewBox="0 0 46 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="1" y="1" width="44" height="32" rx="6" fill="#F8FAFC" fill-opacity="0.16" stroke="#E2E8F0"/>
                                <rect x="9" y="10" width="28" height="3" rx="1.5" fill="#F8FAFC" fill-opacity="0.85"/>
                                <rect x="9" y="17" width="20" height="3" rx="1.5" fill="#F8FAFC" fill-opacity="0.65"/>
                            </svg>
                            <div class="text-end">
                                <div class="text-white/80 small">{{ $account->institution }}</div>
                                <div class="fw-semibold">{{ $account->name }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="num text-3xl fw-bold">{{ number_format((float)$account->current_balance,2,',',' ') }} EUR</div>
                            <div class="d-flex justify-content-between small text-white/75 mt-2">
                                <span>Projection J+30</span>
                                <span class="num {{ $projection['delta_30'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">{{ $projection['delta_30'] >= 0 ? '+' : '' }}{{ number_format($projection['delta_30'],2,',',' ') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="accountAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-3">Ajouter un compte</h5>
        <form class="row g-2" method="POST" action="{{ route('accounts.upsert') }}">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
            <div class="col-12"><input class="form-control" name="name" placeholder="Nom du compte" required></div>
            <div class="col-12"><input class="form-control" name="institution" placeholder="Banque/Institution"></div>
            <div class="col-6"><select class="form-select" name="type" required><option value="checking">Courant</option><option value="savings">Epargne</option><option value="investment">Investissement</option><option value="business">Business</option></select></div>
            <div class="col-6"><input class="form-control" name="currency" value="EUR"></div>
            <div class="col-4"><input class="form-control" name="current_balance" type="number" step="0.01" value="0" required></div>
            <div class="col-4"><input class="form-control" name="ceiling_amount" type="number" step="0.01" placeholder="Plafond"></div>
            <div class="col-4"><input class="form-control" name="target_amount" type="number" step="0.01" placeholder="Objectif"></div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Ajouter</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="accountEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-3">Modifier compte</h5>
        <form class="row g-2" method="POST" action="{{ route('accounts.upsert') }}" id="accountEditForm">
            @csrf
            <input type="hidden" name="account_id" id="edit-account-id">
            <input type="hidden" name="redirect_to" value="{{ request()->url() }}">

            <div class="col-12"><input class="form-control" id="edit-account-name" name="name" required></div>
            <div class="col-12"><input class="form-control" id="edit-account-institution" name="institution" placeholder="Banque/Institution"></div>
            <div class="col-6"><select class="form-select" id="edit-account-type" name="type" required><option value="checking">Courant</option><option value="savings">Epargne</option><option value="investment">Investissement</option><option value="business">Business</option></select></div>
            <div class="col-6"><input class="form-control" id="edit-account-currency" name="currency"></div>
            <div class="col-4"><input class="form-control" id="edit-account-balance" name="current_balance" type="number" step="0.01" required></div>
            <div class="col-4"><input class="form-control" id="edit-account-ceiling" name="ceiling_amount" type="number" step="0.01" placeholder="Plafond"></div>
            <div class="col-4"><input class="form-control" id="edit-account-target" name="target_amount" type="number" step="0.01" placeholder="Objectif"></div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
        </form>

        <form method="POST" id="accountDeleteForm" class="mt-2">
            @csrf
            @method('DELETE')
            <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
            <button class="btn btn-outline-danger w-100">Supprimer ce compte</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const modalEl = document.getElementById('accountEditModal');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  const deleteForm = document.getElementById('accountDeleteForm');

  document.querySelectorAll('.account-edit-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.accountId;
      if (!id) return;

      document.getElementById('edit-account-id').value = id;
      document.getElementById('edit-account-name').value = btn.dataset.accountName || '';
      document.getElementById('edit-account-institution').value = btn.dataset.accountInstitution || '';
      document.getElementById('edit-account-type').value = btn.dataset.accountType || 'checking';
      document.getElementById('edit-account-currency').value = btn.dataset.accountCurrency || 'EUR';
      document.getElementById('edit-account-balance').value = btn.dataset.accountBalance || 0;
      document.getElementById('edit-account-ceiling').value = btn.dataset.accountCeiling || '';
      document.getElementById('edit-account-target').value = btn.dataset.accountTarget || '';
      deleteForm.action = `/accounts/${id}`;
      modal.show();
    });
  });
})();
</script>
@endpush
