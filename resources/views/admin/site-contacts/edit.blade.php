@extends('layouts.dashboard')
@section('title', 'Coordonnées du site — ' . site_name())
@section('page_title', 'Coordonnées du site')

@section('content')
<style>
/* Mise en page : deux colonnes équilibrées, une seule sur écran étroit */
.sc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.25rem;align-items:start}
.sc-col{display:flex;flex-direction:column;gap:1.25rem;min-width:0}
@media (max-width:1100px){.sc-grid{grid-template-columns:minmax(0,1fr)}}
.sc-card{margin:0 !important}
.sc-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.9rem 1rem}
.sc-fields .sc-full{grid-column:1 / -1}
@media (max-width:560px){.sc-fields{grid-template-columns:minmax(0,1fr)}}
.sc-hint{font-size:.74rem;color:var(--c-muted);margin-top:.3rem;line-height:1.45}
.sc-intro{font-size:.8rem;color:var(--c-muted);margin:0 0 .9rem;line-height:1.5}
.sc-mono{font-family:ui-monospace,monospace;letter-spacing:.05em;text-transform:uppercase}

/* Logos / signature : tuiles d'envoi */
.sc-uploads{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
@media (max-width:560px){.sc-uploads{grid-template-columns:minmax(0,1fr)}}
.sc-up{border:1px dashed var(--c-border);border-radius:12px;padding:.9rem;display:flex;flex-direction:column;gap:.6rem;min-width:0}
.sc-up__prev{height:76px;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f0f2f5}
.sc-up__prev--dark{background:#312B81}
.sc-up__prev img{max-height:60px;max-width:90%;object-fit:contain}
.sc-up__empty{font-size:.74rem;color:var(--c-muted)}
.sc-up__rm{display:flex;align-items:center;gap:.4rem;font-size:.78rem;cursor:pointer}
.sc-up input[type=file]{width:100%;font-size:.8rem}

/* Interrupteurs */
.sw-row{display:flex;align-items:flex-start;gap:.9rem;padding:.85rem 0;border-bottom:1px solid var(--c-border);cursor:pointer;position:relative}
.sw-row:first-of-type{padding-top:0}
.sw-row:last-child{border-bottom:0;padding-bottom:0}
.sw-input{position:absolute;opacity:0;pointer-events:none}
.sw-track{flex:none;width:46px;height:26px;border-radius:999px;background:var(--c-border);position:relative;transition:background .2s;margin-top:.1rem}
.sw-thumb{position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.3);transition:transform .2s}
.sw-input:checked + .sw-track{background:#16a34a}
.sw-input:checked + .sw-track .sw-thumb{transform:translateX(20px)}
.sw-input:focus-visible + .sw-track{outline:2px solid var(--c-accent);outline-offset:2px}
.sw-text{display:flex;flex-direction:column;gap:.15rem;font-size:.85rem;min-width:0}
.sw-text small{color:var(--c-muted);font-size:.75rem;line-height:1.45}

/* Barre d'enregistrement */
.sc-save{position:sticky;bottom:0;z-index:5;margin-top:1.25rem;padding:.85rem 0;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  background:var(--c-bg);border-top:1px solid var(--c-border)}
.sc-save span{font-size:.78rem;color:var(--c-muted)}
</style>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 page-hdr">
  <div>
    <h4>Coordonnées du site</h4>
    <p>Identité, coordonnées affichées sur le site public, sécurité de connexion et informations bancaires par défaut</p>
  </div>
</div>

@if($errors->any())
<div class="flash flash-err mb-4"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

<form data-confirm="Enregistrer les coordonnées et les paramètres de connexion du site ? Ils s’appliquent immédiatement à tout le site." data-confirm-title="Paramètres du site" data-confirm-ok="Enregistrer" action="{{ route('admin.site-contacts.update') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="sc-grid">

  {{-- ═════ Colonne gauche : identité et coordonnées publiques ═════ --}}
  <div class="sc-col">

    <div class="card-pro sc-card">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Identité du site</div></div>
      <div class="card-pro-body">
        <div style="margin-bottom:1rem">
          <label class="form-label-pro">Nom du site *</label>
          <input type="text" name="name" class="form-control-pro" value="{{ old('name', $contact->name) }}" required>
        </div>

        <div class="sc-uploads">
          <div class="sc-up">
            <label class="form-label-pro" style="margin:0">Logo — fond clair</label>
            <div class="sc-up__prev">
              @if($contact->logo_light_path)<img src="{{ Storage::url($contact->logo_light_path) }}" alt="Logo fond clair">@else<span class="sc-up__empty">Aucun logo</span>@endif
            </div>
            <input type="file" name="logo_light" accept="image/*">
            @if($contact->logo_light_path)<label class="sc-up__rm"><input type="checkbox" name="remove_logo_light" value="1"> Supprimer ce logo</label>@endif
          </div>
          <div class="sc-up">
            <label class="form-label-pro" style="margin:0">Logo — fond sombre</label>
            <div class="sc-up__prev sc-up__prev--dark">
              @if($contact->logo_dark_path)<img src="{{ Storage::url($contact->logo_dark_path) }}" alt="Logo fond sombre">@else<span class="sc-up__empty" style="color:#9ca3af">Aucun logo</span>@endif
            </div>
            <input type="file" name="logo_dark" accept="image/*">
            @if($contact->logo_dark_path)<label class="sc-up__rm"><input type="checkbox" name="remove_logo_dark" value="1"> Supprimer ce logo</label>@endif
          </div>
        </div>
        <div class="sc-hint">Le logo « fond clair » est aussi utilisé sur les factures PDF. Une seule variante suffit : elle sert pour les deux fonds.</div>
      </div>
    </div>

    <div class="card-pro sc-card">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Coordonnées publiques</div></div>
      <div class="card-pro-body">
        <p class="sc-intro">Affichées dans le pied de page, la page contact et les e-mails du site.</p>
        <div class="sc-fields">
          <div class="sc-full">
            <label class="form-label-pro">Adresse 1 *</label>
            <input type="text" name="address_1" class="form-control-pro" value="{{ old('address_1', $contact->address_1) }}">
          </div>
          <div class="sc-full">
            <label class="form-label-pro">Adresse 2</label>
            <input type="text" name="address_2" class="form-control-pro" value="{{ old('address_2', $contact->address_2) }}">
          </div>
          <div class="sc-full">
            <label class="form-label-pro">Adresse 3</label>
            <input type="text" name="address_3" class="form-control-pro" value="{{ old('address_3', $contact->address_3) }}">
          </div>
          <div>
            <label class="form-label-pro">Téléphone 1</label>
            <input type="text" name="phone_1" class="form-control-pro" value="{{ old('phone_1', $contact->phone_1) }}">
          </div>
          <div>
            <label class="form-label-pro">Téléphone 2</label>
            <input type="text" name="phone_2" class="form-control-pro" value="{{ old('phone_2', $contact->phone_2) }}">
          </div>
          <div class="sc-full">
            <label class="form-label-pro">Adresse e-mail</label>
            <input type="email" name="email" class="form-control-pro" value="{{ old('email', $contact->email) }}" placeholder="contact@exemple.com">
          </div>
        </div>
      </div>
    </div>

    <div class="card-pro sc-card">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Signature des e-mails</div></div>
      <div class="card-pro-body">
        <p class="sc-intro">Image de signature (manuscrite ou scannée) affichée en bas de tous les e-mails envoyés par l'application, avec l'adresse et l'e-mail ci-dessus.</p>
        <div class="sc-up">
          <div class="sc-up__prev" style="height:90px">
            @if($contact->email_signature_path)<img src="{{ Storage::url($contact->email_signature_path) }}" alt="Signature e-mail" style="max-height:74px">@else<span class="sc-up__empty">Aucune signature</span>@endif
          </div>
          <input type="file" name="email_signature" accept="image/*">
          @if($contact->email_signature_path)<label class="sc-up__rm"><input type="checkbox" name="remove_email_signature" value="1"> Supprimer cette signature</label>@endif
        </div>
      </div>
    </div>

  </div>

  {{-- ═════ Colonne droite : sécurité, banque, WhatsApp ═════ --}}
  <div class="sc-col">

    <div class="card-pro sc-card">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Connexion et e-mails</div></div>
      <div class="card-pro-body">
        <label class="sw-row">
          <input type="hidden" name="otp_clients_enabled" value="0">
          <input type="checkbox" name="otp_clients_enabled" value="1" class="sw-input" {{ old('otp_clients_enabled', $contact->otp_clients_enabled) ? 'checked' : '' }}>
          <span class="sw-track" aria-hidden="true"><span class="sw-thumb"></span></span>
          <span class="sw-text"><strong>Code OTP à la connexion — clients</strong><small>Activé : chaque client reçoit un code par e-mail à chaque connexion. Désactivé : connexion directe, aucun e-mail OTP.</small></span>
        </label>
        <label class="sw-row">
          <input type="hidden" name="otp_staff_enabled" value="0">
          <input type="checkbox" name="otp_staff_enabled" value="1" class="sw-input" {{ old('otp_staff_enabled', $contact->otp_staff_enabled) ? 'checked' : '' }}>
          <span class="sw-track" aria-hidden="true"><span class="sw-thumb"></span></span>
          <span class="sw-text"><strong>Code OTP à la connexion — personnel</strong><small>Activé : administrateurs et super-administrateurs reçoivent un code par e-mail. Désactivé : connexion directe (moins sécurisé).</small></span>
        </label>
        <label class="sw-row">
          <input type="hidden" name="activation_mail_enabled" value="0">
          <input type="checkbox" name="activation_mail_enabled" value="1" class="sw-input" {{ old('activation_mail_enabled', $contact->activation_mail_enabled) ? 'checked' : '' }}>
          <span class="sw-track" aria-hidden="true"><span class="sw-thumb"></span></span>
          <span class="sw-text"><strong>E-mail d’activation du compte</strong><small>Activé : tout nouvel inscrit reçoit un e-mail d’activation. Désactivé : aucun e-mail n’est envoyé et le compte est activé automatiquement.</small></span>
        </label>
      </div>
    </div>

    <div class="card-pro sc-card">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Coordonnées bancaires par défaut</div></div>
      <div class="card-pro-body">
        <p class="sc-intro" style="margin-bottom:.7rem"><strong>IBAN générés automatiquement</strong> à l'approbation d'un compte</p>
        <div class="sc-fields">
          <div>
            <label class="form-label-pro">BIC par défaut</label>
            <input type="text" name="default_bic" class="form-control-pro sc-mono" maxlength="11"
                   value="{{ old('default_bic', $contact->default_bic) }}" placeholder="{{ app(\App\Services\BankingProvisioner::class)->defaultBic() }}">
            <div class="sc-hint">8 ou 11 caractères. Vide : valeur proposée.</div>
          </div>
          <div>
            <label class="form-label-pro">Code banque (BLZ)</label>
            <input type="text" name="iban_bank_code" class="form-control-pro sc-mono" maxlength="8" inputmode="numeric"
                   value="{{ old('iban_bank_code', $contact->iban_bank_code) }}" placeholder="{{ app(\App\Services\BankingProvisioner::class)->bankCode() }}">
            <div class="sc-hint">8 chiffres (IBAN allemand, DE).</div>
          </div>
        </div>

        <hr style="border:0;border-top:1px solid var(--c-border);margin:1.1rem 0">

        <p class="sc-intro" style="margin-bottom:.7rem"><strong>Règlement des factures</strong> — pré-rempli dans les nouvelles factures et dans « Facturer les frais », modifiable facture par facture</p>
        <div class="sc-fields">
          <div class="sc-full">
            <label class="form-label-pro">IBAN de règlement</label>
            <input type="text" name="payment_iban" class="form-control-pro sc-mono" maxlength="40"
                   value="{{ old('payment_iban', $contact->payment_iban) }}" placeholder="DE00 0000 0000 0000 0000 00">
          </div>
          <div>
            <label class="form-label-pro">Bénéficiaire de l’IBAN</label>
            <input type="text" name="payment_holder" class="form-control-pro" maxlength="100"
                   value="{{ old('payment_holder', $contact->payment_holder) }}" placeholder="{{ site_name() }}">
          </div>
          <div>
            <label class="form-label-pro">Type de virement par défaut</label>
            <select name="payment_type" class="form-control-pro">
              <option value="sepa" {{ old('payment_type', $contact->payment_type ?: 'sepa') === 'sepa' ? 'selected' : '' }}>Virement SEPA</option>
              <option value="instant" {{ old('payment_type', $contact->payment_type) === 'instant' ? 'selected' : '' }}>Virement en temps réel</option>
            </select>
          </div>
          <div>
            <label class="form-label-pro">BIC de règlement</label>
            <input type="text" name="payment_bic" class="form-control-pro sc-mono" maxlength="11"
                   value="{{ old('payment_bic', $contact->payment_bic) }}" placeholder="SOLBDEFF">
          </div>
        </div>
      </div>
    </div>

    <div class="card-pro sc-card">
      <div class="card-pro-hdr"><div class="card-pro-title"><span class="icon-dot"></span>Assistant WhatsApp</div></div>
      <div class="card-pro-body">
        <p class="sc-intro">Bulle flottante affichée sur le site public, en bas de l'écran, qui ouvre une conversation WhatsApp. Visible uniquement si un numéro est renseigné et l'assistant activé.</p>
        <div style="margin-bottom:.9rem">
          <label class="form-label-pro">Numéro WhatsApp</label>
          <input type="text" name="whatsapp_number" class="form-control-pro"
                 value="{{ old('whatsapp_number', $contact->whatsapp_number) }}" placeholder="+33612345678">
          <div class="sc-hint">Format international, avec l'indicatif pays (ex : +33612345678).</div>
        </div>
        <label class="sw-row" style="padding:.2rem 0">
          <input type="hidden" name="whatsapp_enabled" value="0">
          <input type="checkbox" name="whatsapp_enabled" value="1" class="sw-input" {{ old('whatsapp_enabled', $contact->whatsapp_enabled) ? 'checked' : '' }}>
          <span class="sw-track" aria-hidden="true"><span class="sw-thumb"></span></span>
          <span class="sw-text"><strong>Afficher la bulle WhatsApp sur le site public</strong></span>
        </label>
      </div>
    </div>

  </div>
</div>

<div class="sc-save">
  <span><i class="fas fa-circle-info"></i> Les modifications s'appliquent à tout le site dès l'enregistrement.</span>
  <button type="submit" class="btn-navy"><i class="fas fa-save"></i> Enregistrer</button>
</div>

</form>
@endsection
