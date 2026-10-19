<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteContactController extends Controller
{
    public function edit()
    {
        $contact = SiteContact::current();
        return view('admin.site-contacts.edit', compact('contact'));
    }

    public function update(Request $request)
    {
        // BIC normalisé avant validation : majuscules, sans espaces ni tirets
        if ($request->has('default_bic')) {
            $request->merge(['default_bic' => strtoupper(preg_replace('/[[:space:]-]+/', '', (string) $request->input('default_bic'))) ?: null]);
        }

        $request->merge([
            'payment_iban' => strtoupper(preg_replace('/[\s\x{00A0}]+/u', '', (string) $request->input('payment_iban'))) ?: null,
            'payment_bic'  => strtoupper(preg_replace('/[[:space:]-]+/', '', (string) $request->input('payment_bic'))) ?: null,
        ]);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'address_3' => ['nullable', 'string', 'max:255'],
            'phone_1'   => ['nullable', 'string', 'max:255'],
            'phone_2'   => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'default_bic'      => ['nullable', 'string', 'regex:/^[A-Z0-9]{8}([A-Z0-9]{3})?$/'],
            'iban_bank_code'   => ['nullable', 'digits:8'],
            'payment_iban'     => ['nullable', 'string', 'max:40', new \App\Rules\ValidIban()],
            'payment_bic'      => ['nullable', 'string', 'regex:/^[A-Z0-9]{8}([A-Z0-9]{3})?$/'],
            'payment_holder'   => ['nullable', 'string', 'max:100'],
            'payment_type'     => ['nullable', 'in:sepa,instant'],
            'whatsapp_number'  => ['nullable', 'string', 'max:30'],
            'whatsapp_enabled' => ['boolean'],
            'otp_clients_enabled'     => ['boolean'],
            'otp_staff_enabled'       => ['boolean'],
            'activation_method'       => ['required', 'in:none,link,otp'],
            'logo_light'      => ['nullable', 'image', 'max:2048'],
            'logo_dark'       => ['nullable', 'image', 'max:2048'],
            'email_signature' => ['nullable', 'image', 'max:2048'],
        ], [
            'default_bic.regex' => 'Le BIC doit contenir 8 ou 11 caractères (lettres et chiffres), ex. SOLBDEFF.',
        ]);

        $contact = SiteContact::current();
        $contact->update(collect($validated)->except(['logo_light', 'logo_dark', 'email_signature', 'activation_method'])->all() + [
            // Une seule méthode de validation du compte à la fois : code OTP, lien par e-mail, ou aucune (activation automatique)
            'activation_otp_enabled'  => $validated['activation_method'] === 'otp',
            'activation_mail_enabled' => $validated['activation_method'] === 'link',
        ]);

        $this->handleLogoUploads($request, $contact);

        return back()->with('success', 'Coordonnées mises à jour.');
    }

    private function handleLogoUploads(Request $request, SiteContact $contact): void
    {
        $logoFields = [
            'logo_light'      => 'logo_light_path',
            'logo_dark'       => 'logo_dark_path',
            'email_signature' => 'email_signature_path',
        ];

        foreach ($logoFields as $input => $column) {
            if ($request->hasFile($input)) {
                $old = $contact->$column;
                $contact->$column = $request->file($input)->store('site-branding', 'public');
                if ($old) Storage::disk('public')->delete($old);
            } elseif ($request->boolean('remove_' . $input) && $contact->$column) {
                Storage::disk('public')->delete($contact->$column);
                $contact->$column = null;
            }
        }

        $contact->save();
    }
}
