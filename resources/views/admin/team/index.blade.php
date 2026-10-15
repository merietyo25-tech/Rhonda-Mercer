@extends('layouts.dashboard')
@section('title', 'Équipe du site')
@section('page_title', 'Équipe du site')

@section('content')
@push('styles')
<style>
[x-cloak]{display:none !important}
.tm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem}
.tm-card{background:#fff;border:1px solid var(--c-border);border-radius:14px;padding:1.25rem;display:flex;flex-direction:column;gap:.75rem}
.tm-card__top{display:flex;gap:.9rem;align-items:center}
.tm-av{width:58px;height:58px;border-radius:50%;flex-shrink:0;background:var(--c-navy);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;background-size:cover;background-position:center}
.tm-name{font-weight:800;color:var(--c-navy)}
.tm-title{font-size:.8rem;color:var(--c-muted)}
.tm-bio{font-size:.82rem;color:var(--c-text);line-height:1.55;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden}
.tm-acts{display:flex;gap:.5rem;margin-top:auto}
.am{position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem}
.am__bg{position:absolute;inset:0;background:rgba(2,24,46,.55);backdrop-filter:blur(2px)}
.am__box{position:relative;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;background:#fff;border-radius:18px;box-shadow:0 24px 60px rgba(2,24,46,.3);padding:1.6rem 1.7rem 1.4rem}
.am__x{position:absolute;top:.9rem;right:.9rem;width:32px;height:32px;border-radius:50%;border:0;background:var(--c-bg);color:var(--c-muted);cursor:pointer}
.am__title{font-size:1.0625rem;font-weight:800;color:var(--c-navy);margin:0 0 1rem}
.am__grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem}
.am__grid .full{grid-column:1/-1}
.am__foot{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.1rem}
@media (max-width:560px){.am__grid{grid-template-columns:1fr}}
</style>
@endpush

<div x-data="{
  open: false, edit: null,
  blank: { id: null, name: '', title: '', bio: '', sort_order: 0, is_leader: false, is_visible: true, photo_url: null },
  f: null,
  add(){ this.f = JSON.parse(JSON.stringify(this.blank)); this.open = true; },
  modify(m){ this.f = JSON.parse(JSON.stringify(m)); this.open = true; },
  action(){ return this.f && this.f.id ? '{{ url('admin/team') }}/' + this.f.id : '{{ route('admin.team.store') }}'; }
}" @keydown.escape.window="open = false">

  <div class="page-hdr-row">
    <div class="page-hdr">
      <h1>Équipe du site</h1>
      <p>Membres présentés publiquement sur la page « À propos » (direction et équipe). Les membres « direction » sont mis en avant avec leur biographie.</p>
    </div>
    <button type="button" class="btn-accent btn-sm-pro" @click="add()"><i class="fas fa-plus"></i> Ajouter un membre</button>
  </div>

  @if($errors->any())
  <div class="flash flash-err" style="margin-bottom:1rem"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
  @endif

  @if($members->isEmpty())
  <div class="card-pro" style="padding:3rem;text-align:center;color:var(--c-muted)">Aucun membre. La section « membres » n’apparaît pas sur la page À propos tant qu’il n’y en a pas.</div>
  @else
  <div class="tm-grid">
    @foreach($members as $m)
    @php
      $payload = ['id' => $m->id, 'name' => $m->name, 'title' => $m->title, 'bio' => $m->bio, 'sort_order' => $m->sort_order, 'is_leader' => $m->is_leader, 'is_visible' => $m->is_visible, 'photo_url' => $m->photoUrl()];
    @endphp
    <article class="tm-card" style="{{ $m->is_visible ? '' : 'opacity:.6' }}">
      <div class="tm-card__top">
        <div class="tm-av" @if($m->photoUrl()) style="background-image:url('{{ $m->photoUrl() }}')" @endif>@unless($m->photoUrl()){{ $m->initials() }}@endunless</div>
        <div>
          <div class="tm-name">{{ $m->name }}</div>
          <div class="tm-title">{{ $m->title }}</div>
          <div style="margin-top:.3rem;display:flex;gap:.3rem;flex-wrap:wrap">
            @if($m->is_leader)<span class="badge-status bs-violet">Direction</span>@endif
            @unless($m->is_visible)<span class="badge-status bs-gray">Masqué</span>@endunless
          </div>
        </div>
      </div>
      @if($m->bio)<div class="tm-bio">{{ $m->bio }}</div>@endif
      <div class="tm-acts">
        <button type="button" class="btn-ghost btn-sm-pro" @click='modify(@json($payload))'><i class="fas fa-pen"></i> Modifier</button>
        <form method="POST" action="{{ route('admin.team.destroy', $m) }}" data-confirm="Supprimer {{ $m->name }} de la page À propos ?" data-confirm-title="Supprimer le membre" data-confirm-ok="Supprimer" data-confirm-danger="1">
          @csrf @method('DELETE')
          <button type="submit" class="btn-ghost btn-sm-pro" style="color:var(--c-red)"><i class="fas fa-trash"></i></button>
        </form>
      </div>
    </article>
    @endforeach
  </div>
  @endif

  {{-- Modale : ajout / modification --}}
  <div class="am" x-show="open" x-cloak x-transition.opacity>
    <div class="am__bg" @click="open = false"></div>
    <div class="am__box" @click.stop>
      <button type="button" class="am__x" @click="open = false" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
      <h3 class="am__title" x-text="f && f.id ? 'Modifier le membre' : 'Ajouter un membre'"></h3>
      <form method="POST" :action="action()" enctype="multipart/form-data" x-show="f">
        @csrf
        <template x-if="f && f.id"><input type="hidden" name="_method" value="PUT"></template>
        <div class="am__grid">
          <div>
            <label class="form-label-pro">Nom complet *</label>
            <input type="text" name="name" class="form-control-pro" maxlength="120" required x-model="f.name">
          </div>
          <div>
            <label class="form-label-pro">Fonction *</label>
            <input type="text" name="title" class="form-control-pro" maxlength="120" required x-model="f.title" placeholder="Ex : Directeur général">
          </div>
          <div class="full">
            <label class="form-label-pro">Biographie</label>
            <textarea name="bio" class="form-control-pro" rows="5" maxlength="1500" x-model="f.bio" placeholder="Parcours, rôle, engagements…"></textarea>
          </div>
          <div class="full">
            <label class="form-label-pro">Photo (JPG, PNG ou WebP, 3 Mo max)</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-control-pro">
            <label x-show="f && f.photo_url" style="display:flex;gap:.5rem;align-items:center;margin-top:.5rem;font-size:.8rem"><input type="checkbox" name="remove_photo" value="1"> Retirer la photo actuelle</label>
          </div>
          <div>
            <label class="form-label-pro">Ordre d’affichage</label>
            <input type="number" name="sort_order" class="form-control-pro" min="0" max="999" x-model="f.sort_order">
          </div>
          <div style="display:flex;flex-direction:column;gap:.5rem;justify-content:flex-end">
            <label style="display:flex;gap:.5rem;align-items:center;font-size:.85rem"><input type="checkbox" name="is_leader" value="1" :checked="f && f.is_leader"> Membre de la direction (mis en avant)</label>
            <label style="display:flex;gap:.5rem;align-items:center;font-size:.85rem"><input type="checkbox" name="is_visible" value="1" :checked="f && f.is_visible"> Visible sur le site</label>
          </div>
        </div>
        <div class="am__foot">
          <button type="button" class="btn-ghost btn-sm-pro" @click="open = false">Annuler</button>
          <button type="submit" class="btn-accent btn-sm-pro"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
