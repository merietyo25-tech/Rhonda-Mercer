@extends('layouts.dashboard')
@section('title', 'Champs de vérification d’identité — ' . site_name())
@section('page_title', 'Formulaire KYC')

@section('content')
<style>
[x-cloak]{display:none !important}
.kf-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1.25rem;align-items:start}
@media (max-width:1100px){.kf-grid{grid-template-columns:minmax(0,1fr)}}
.kf-steps{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.kf-step{display:flex;gap:.75rem;align-items:flex-start;border:1.5px solid var(--c-border);border-radius:12px;padding:.9rem 1rem;cursor:pointer;position:relative}
.kf-step input{position:absolute;opacity:0;pointer-events:none}
.kf-step:has(input:checked){border-color:var(--c-accent);background:rgba(0,132,87,.08)}
.kf-step__n{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;background:var(--c-border);flex:none}
.kf-step:has(input:checked) .kf-step__n{background:var(--c-accent);color:#fff}
.kf-step strong{display:block;font-size:.9rem}
.kf-step small{color:var(--c-muted);font-size:.74rem;line-height:1.4}
.kf-row{display:grid;grid-template-columns:minmax(0,1fr) 88px 78px auto auto;gap:.75rem;align-items:center;padding:.8rem 1.1rem;border-top:1px solid var(--c-border)}
.kf-row.is-off{opacity:.55}
@media (max-width:760px){.kf-row{grid-template-columns:1fr 1fr;}.kf-row > :first-child{grid-column:1 / -1}}
.kf-name{font-weight:700;font-size:.88rem;min-width:0;overflow-wrap:anywhere}
.kf-tag{display:inline-block;font-size:.64rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.1rem .5rem;border-radius:999px;margin-left:.35rem;vertical-align:middle;background:var(--c-border);color:var(--c-muted)}
.kf-tag--custom{background:rgba(27,73,118,.12);color:#3A3590}
.kf-mini{font-size:.68rem;color:var(--c-muted);display:block;margin-bottom:.15rem;text-transform:uppercase;letter-spacing:.04em}
.kf-row select,.kf-row input[type=number]{width:100%}
.kf-sw{display:flex;align-items:center;gap:.4rem;font-size:.78rem;cursor:pointer;white-space:nowrap}
.kf-sw input{width:auto}
.kf-extra{grid-column:1 / -1;display:grid;grid-template-columns:1fr 1fr;gap:.75rem;padding-top:.2rem}
@media (max-width:640px){.kf-extra{grid-template-columns:1fr}}
.kf-i18n{border:1px dashed var(--c-border);border-radius:10px;padding:.5rem .8rem}
.kf-i18n summary{cursor:pointer;font-size:.8rem;font-weight:700;list-style:none}
.kf-i18n summary::-webkit-details-marker{display:none}
.kf-i18n[open] summary{margin-bottom:.6rem}
.kf-i18n__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:.7rem}
.kf-i18n__grid em{font-style:normal;opacity:.7}
.kf-auto{font-size:.6rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;padding:.05rem .4rem;border-radius:999px;background:rgba(27,73,118,.12);color:#3A3590;margin-left:.2rem}
.kf-hint{font-size:.72rem;color:var(--c-muted);margin-top:.6rem;line-height:1.45}
.kf-save{position:sticky;bottom:0;z-index:5;margin-top:1rem;padding:.85rem 0;display:flex;justify-content:flex-end;background:var(--c-bg);border-top:1px solid var(--c-border)}
</style>

<div class="page-hdr-row">
  <div class="page-hdr">
    <h1>Champs de vérification d’identité</h1>
    <p>Choisissez les champs demandés au client, s’ils sont obligatoires, leur ordre, et si la vérification se fait en une ou en deux étapes.</p>
  </div>
  <a href="{{ route('admin.kyc.index') }}" class="btn-ghost btn-sm-pro"><i class="fas fa-arrow-left"></i> Vérifications</a>
</div>

@if($errors->any())
<div class="flash flash-err mb-4"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

<div class="kf-grid" x-data="{ steps: '{{ old('kyc_steps', $steps) }}' }">

  <form id="kf-form" method="POST" action="{{ route('admin.kyc.fields.update') }}"
        data-confirm="Enregistrer la configuration du formulaire de vérification ? Elle s’applique immédiatement aux clients qui n’ont pas encore transmis leur dossier." data-confirm-title="Formulaire KYC" data-confirm-ok="Enregistrer">
    @csrf

    <div class="card-pro mb-4">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Nombre d’étapes</div></div>
      <div class="card-pro-body">
        <div class="kf-steps">
          <label class="kf-step"><input type="radio" name="kyc_steps" value="1" x-model="steps">
            <span class="kf-step__n">1</span><span><strong>Une seule étape</strong><small>Tous les champs actifs sur la même page, un seul envoi.</small></span></label>
          <label class="kf-step"><input type="radio" name="kyc_steps" value="2" x-model="steps">
            <span class="kf-step__n">2</span><span><strong>Deux étapes</strong><small>Chaque champ est rangé dans l’étape 1 ou l’étape 2 (ex. informations, puis documents).</small></span></label>
        </div>
      </div>
    </div>

    <div class="card-pro">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Champs</div></div>
      @foreach($fields as $f)
      <div class="kf-row {{ $f->enabled ? '' : 'is-off' }}">
        <div class="kf-name">
          @if($f->builtin){{ $f->displayLabel() }}<span class="kf-tag">Natif</span>
          @else
            <input type="text" name="fields[{{ $f->id }}][label]" class="form-control-pro" value="{{ old('fields.' . $f->id . '.label', $f->label) }}" maxlength="150" required>
            <span class="kf-tag kf-tag--custom" style="margin:.35rem 0 0">Personnalisé · {{ \App\Models\KycField::CUSTOM_TYPES[$f->type] ?? $f->type }}</span>
          @endif
          <div style="font-size:.7rem;color:var(--c-muted);font-weight:400;margin-top:.2rem">
            @switch($f->type)
              @case('file') Fichier image ou PDF @break
              @case('image') Photo (image uniquement) @break
              @case('date') Date @break
              @case('country') Liste des pays (détection automatique) @break
              @case('doc_type') Carte d’identité, passeport ou permis @break
              @case('select') Liste de choix @break
              @case('textarea') Texte long @break
              @default Texte
            @endswitch
          </div>
        </div>
        <div x-show="steps === '2'" x-cloak>
          <span class="kf-mini">Étape</span>
          <select name="fields[{{ $f->id }}][step]" class="form-control-pro" x-bind:disabled="steps !== '2'">
            <option value="1" @selected(old('fields.' . $f->id . '.step', $f->step) == 1)>Étape 1</option>
            <option value="2" @selected(old('fields.' . $f->id . '.step', $f->step) == 2)>Étape 2</option>
          </select>
        </div>
        <input type="hidden" name="fields[{{ $f->id }}][step]" value="{{ $f->step }}" x-bind:disabled="steps === '2'">
        <div>
          <span class="kf-mini">Ordre</span>
          <input type="number" min="0" max="9999" name="fields[{{ $f->id }}][sort]" class="form-control-pro" value="{{ old('fields.' . $f->id . '.sort', $f->sort) }}">
        </div>
        <label class="kf-sw"><input type="checkbox" name="fields[{{ $f->id }}][enabled]" value="1" @checked(old('fields', null) ? ! empty(old('fields.' . $f->id . '.enabled')) : $f->enabled)> Actif</label>
        <label class="kf-sw"><input type="checkbox" name="fields[{{ $f->id }}][required]" value="1" @checked(old('fields', null) ? ! empty(old('fields.' . $f->id . '.required')) : $f->required)> Obligatoire</label>

        @if(! $f->builtin)
        <details class="kf-i18n" style="grid-column:1 / -1">
          <summary><i class="fas fa-language"></i> Traductions ({{ count(array_filter($f->labels ?? [])) }}/{{ $languages->count() }} langues)</summary>
          <div class="kf-i18n__grid">
            @foreach($languages as $lang)
            <div>
              <span class="kf-mini">{{ $lang->native_name }} <em>({{ $lang->code }})</em>@if(in_array($lang->code, $f->auto_locales ?? [], true)) <span class="kf-auto" title="Traduit automatiquement — modifiez le texte pour le corriger">auto</span>@endif</span>
              <input type="text" name="fields[{{ $f->id }}][labels][{{ $lang->code }}]" class="form-control-pro" maxlength="150"
                     value="{{ old('fields.' . $f->id . '.labels.' . $lang->code, ($f->labels ?? [])[$lang->code] ?? '') }}" placeholder="{{ $f->label }}">
              @if($f->type === 'select')
              <textarea name="fields[{{ $f->id }}][options_i18n][{{ $lang->code }}]" rows="3" class="form-control-pro" style="margin-top:.3rem"
                        placeholder="Choix traduits (une ligne par choix, même ordre)">{{ old('fields.' . $f->id . '.options_i18n.' . $lang->code, implode("\n", (($f->options_i18n ?? [])[$lang->code] ?? []))) }}</textarea>
              @endif
            </div>
            @endforeach
          </div>
          <div class="kf-hint">À l’enregistrement, les langues laissées vides sont traduites automatiquement depuis le libellé par défaut (français) ; vous pouvez ensuite corriger n’importe quelle traduction. Le client voit le libellé de sa langue.</div>
        </details>
        <div class="kf-extra">
          @if($f->type === 'select')
          <div><span class="kf-mini">Choix proposés (un par ligne)</span>
            <textarea name="fields[{{ $f->id }}][options]" rows="3" class="form-control-pro">{{ old('fields.' . $f->id . '.options', implode("\n", $f->options ?? [])) }}</textarea></div>
          @else<div></div>@endif
          <div style="text-align:right;align-self:end">
            <button type="submit" form="kf-del-{{ $f->id }}" class="btn-ghost btn-sm-pro" style="color:var(--c-red);border-color:var(--c-red)"><i class="fas fa-trash"></i> Supprimer ce champ</button>
          </div>
        </div>
        @endif
      </div>
      @endforeach
    </div>

    <div class="kf-save"><button type="submit" class="btn-navy"><i class="fas fa-save"></i> Enregistrer</button></div>
  </form>

  {{-- Ajout d'un champ --}}
  <div>
    <form method="POST" action="{{ route('admin.kyc.fields.store') }}" class="card-pro" x-data="{ type: 'text' }"
          data-confirm="Ajouter ce champ au formulaire de vérification ?" data-confirm-title="Nouveau champ" data-confirm-ok="Ajouter">
      @csrf
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Ajouter un champ</div></div>
      <div class="card-pro-body">
        <div style="margin-bottom:.8rem">
          <label class="form-label-pro">Libellé *</label>
          <input type="text" name="label" class="form-control-pro" maxlength="150" required placeholder="Ex : Numéro de sécurité sociale">
        </div>
        <div style="margin-bottom:.8rem">
          <label class="form-label-pro">Type de champ</label>
          <select name="type" class="form-control-pro" x-model="type">
            @foreach(\App\Models\KycField::CUSTOM_TYPES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
          </select>
        </div>
        <div style="margin-bottom:.8rem" x-show="type === 'select'" x-cloak>
          <label class="form-label-pro">Choix proposés (un par ligne)</label>
          <textarea name="options" rows="4" class="form-control-pro" placeholder="Salarié&#10;Indépendant&#10;Retraité"></textarea>
        </div>
        <div style="margin-bottom:.8rem" x-show="steps === '2'" x-cloak>
          <label class="form-label-pro">Étape</label>
          <select name="step" class="form-control-pro" x-bind:disabled="steps !== '2'">
            <option value="1">Étape 1</option><option value="2">Étape 2</option>
          </select>
        </div>
        <input type="hidden" name="step" value="1" x-bind:disabled="steps === '2'">
        <details class="kf-i18n" style="margin-bottom:.9rem">
          <summary><i class="fas fa-language"></i> Traductions du libellé (automatiques si vides)</summary>
          <div class="kf-i18n__grid" style="grid-template-columns:1fr">
            @foreach($languages as $lang)
            <div><span class="kf-mini">{{ $lang->native_name }} ({{ $lang->code }})</span>
              <input type="text" name="labels[{{ $lang->code }}]" class="form-control-pro" maxlength="150">
              <textarea name="options_i18n[{{ $lang->code }}]" rows="2" class="form-control-pro" style="margin-top:.3rem" x-show="type === 'select'" x-cloak placeholder="Choix traduits (une ligne par choix)"></textarea></div>
            @endforeach
          </div>
        </details>
        <label class="kf-sw" style="margin-bottom:1rem"><input type="checkbox" name="required" value="1" checked> Champ obligatoire (décochez pour un champ facultatif)</label>
        <button type="submit" class="btn-accent btn-sm-pro" style="width:100%"><i class="fas fa-plus"></i> Ajouter</button>
        <p style="font-size:.74rem;color:var(--c-muted);margin:.8rem 0 0;line-height:1.5">Chaque champ personnalisé peut être traduit dans toutes les langues du site. Les champs natifs sont déjà traduits.</p>
      </div>
    </form>
  </div>
</div>

{{-- Formulaires de suppression (hors du formulaire principal : les formulaires ne s'imbriquent pas) --}}
@foreach($fields->where('builtin', false) as $f)
<form id="kf-del-{{ $f->id }}" method="POST" action="{{ route('admin.kyc.fields.destroy', $f) }}"
      data-confirm="Supprimer le champ « {{ $f->label }} » ? Les réponses déjà envoyées par les clients (fichiers compris) seront aussi supprimées." data-confirm-title="Supprimer le champ" data-confirm-ok="Supprimer" data-confirm-danger="1">
  @csrf @method('DELETE')
</form>
@endforeach
@endsection
