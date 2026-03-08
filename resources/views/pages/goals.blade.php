@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="text-xl font-bold">Objectifs</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goalAddModal">Ajouter</button>
    </div>

    <div class="grid gap-6">
        @forelse($goals as $goal)
            @php $progress = $goal->target_amount > 0 ? min(100, round(((float)$goal->current_amount / (float)$goal->target_amount) * 100)) : 0; @endphp
            <div class="fp-card p-5">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="font-bold">{{ $goal->title }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format((float)$goal->required_per_period, 2, ',', ' ') }} EUR / periode requis</p>
                    </div>
                    <span class="badge bg-blue-100 text-blue-700 rounded-full px-3 py-1 text-[10px] font-bold">{{ $progress }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <div class="flex justify-between mt-3 text-xs">
                    <span class="num">{{ number_format((float)$goal->current_amount,2,',',' ') }} EUR</span>
                    <span class="num text-slate-400">Cible: {{ number_format((float)$goal->target_amount,2,',',' ') }} EUR</span>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-primary goal-edit-btn"
                        data-goal-id="{{ $goal->id }}"
                        data-goal-title="{{ $goal->title }}"
                        data-goal-type="{{ $goal->type }}"
                        data-goal-cadence="{{ $goal->cadence }}"
                        data-goal-target="{{ $goal->target_amount }}"
                        data-goal-current="{{ $goal->current_amount }}"
                        data-goal-start="{{ $goal->start_date }}"
                        data-goal-end="{{ $goal->target_date }}">Modifier</button>
                    <button class="btn btn-sm btn-outline-secondary archive-goal-btn" data-goal-id="{{ $goal->id }}" data-goal-title="{{ $goal->title }}">Archiver</button>
                    <form method="POST" action="{{ route('goals.destroy', $goal) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="fp-card p-5 text-sm text-slate-500 dark:text-slate-400">Aucun objectif actif.</div>
        @endforelse

        @if(!empty($budgetOverLimit))
            @foreach($budgetOverLimit as $name)
                <div class="fp-card p-5 border-rose-200 dark:border-rose-900/30">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i> Alerte Budget {{ $name }}</h4>
                        <span class="text-[10px] font-bold text-rose-500 uppercase">Alerte 110%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-rose-500 rounded-full" style="width: 100%"></div>
                    </div>
                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-3">Depassement detecte sur ce budget categorie.</p>
                </div>
            @endforeach
        @endif
    </div>
</div>

<div class="modal fade" id="goalAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-3">Ajouter un objectif</h5>
        <form method="POST" action="{{ route('goals.store') }}" class="row g-2">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
            <div class="col-12"><input class="form-control" name="title" placeholder="Ex: Fonds urgence" required></div>
            <div class="col-6"><select class="form-select" name="type" required><option value="saving">Epargne</option><option value="investment">Investissement</option></select></div>
            <div class="col-6"><select class="form-select" name="cadence" required><option value="monthly">Mensuel</option><option value="quarterly">Trimestriel</option><option value="semiannual">Semestriel</option><option value="annual">Annuel</option></select></div>
            <div class="col-6"><input class="form-control" type="number" step="0.01" min="1" name="target_amount" placeholder="Montant cible" required></div>
            <div class="col-6"><input class="form-control" type="number" step="0.01" min="0" name="current_amount" value="0" placeholder="Montant actuel"></div>
            <div class="col-6"><input class="form-control" type="date" name="start_date" value="{{ $today }}"></div>
            <div class="col-6"><input class="form-control" type="date" name="target_date"></div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Ajouter</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="goalEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-3">Modifier objectif</h5>
        <form method="POST" id="goalEditForm" class="row g-2">
          @csrf
          @method('PATCH')
          <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
          <div class="col-12"><input id="goal-title" class="form-control" name="title" required></div>
          <div class="col-6"><select id="goal-type" class="form-select" name="type" required><option value="saving">Epargne</option><option value="investment">Investissement</option></select></div>
          <div class="col-6"><select id="goal-cadence" class="form-select" name="cadence" required><option value="monthly">Mensuel</option><option value="quarterly">Trimestriel</option><option value="semiannual">Semestriel</option><option value="annual">Annuel</option></select></div>
          <div class="col-6"><input id="goal-target" class="form-control" type="number" step="0.01" min="1" name="target_amount" required></div>
          <div class="col-6"><input id="goal-current" class="form-control" type="number" step="0.01" min="0" name="current_amount"></div>
          <div class="col-6"><input id="goal-start" class="form-control" type="date" name="start_date"></div>
          <div class="col-6"><input id="goal-end" class="form-control" type="date" name="target_date"></div>
          <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="archiveGoalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4">
        <h5 class="mb-2">Archiver l'objectif ?</h5>
        <p class="text-secondary small mb-3" id="archiveGoalText"></p>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
          <form method="POST" id="archiveGoalForm">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
            <button class="btn btn-danger">Archiver</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const archiveModalEl = document.getElementById('archiveGoalModal');
  const goalEditEl = document.getElementById('goalEditModal');

  if (archiveModalEl) {
    const archiveModal = new bootstrap.Modal(archiveModalEl);
    const text = document.getElementById('archiveGoalText');
    const form = document.getElementById('archiveGoalForm');

    document.querySelectorAll('.archive-goal-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.goalId;
        const title = btn.dataset.goalTitle;
        form.action = `/goals/${id}/archive`;
        text.textContent = `L'objectif "${title}" sera retire des calculs actifs.`;
        archiveModal.show();
      });
    });
  }

  if (goalEditEl) {
    const goalEditModal = new bootstrap.Modal(goalEditEl);
    const goalEditForm = document.getElementById('goalEditForm');

    document.querySelectorAll('.goal-edit-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const goalId = btn.dataset.goalId;
        if (!goalId || !goalEditForm) return;

        goalEditForm.action = `/goals/${goalId}`;
        document.getElementById('goal-title').value = btn.dataset.goalTitle || '';
        document.getElementById('goal-type').value = btn.dataset.goalType || 'saving';
        document.getElementById('goal-cadence').value = btn.dataset.goalCadence || 'monthly';
        document.getElementById('goal-target').value = btn.dataset.goalTarget || '';
        document.getElementById('goal-current').value = btn.dataset.goalCurrent || '';
        document.getElementById('goal-start').value = btn.dataset.goalStart || '';
        document.getElementById('goal-end').value = btn.dataset.goalEnd || '';

        goalEditModal.show();
      });
    });
  }
})();
</script>
@endpush
