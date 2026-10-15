<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    protected $fillable = ['name', 'title', 'bio', 'photo_path', 'is_leader', 'is_visible', 'sort_order'];

    protected $casts = [
        'is_leader'  => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        // Chemin dans public/ (images livrées avec le site) ou fichier téléversé depuis l'administration (disque public)
        return str_starts_with($this->photo_path, 'assets/') ? asset($this->photo_path) : Storage::url($this->photo_path);
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];

        return mb_strtoupper(mb_substr($words[0] ?? '', 0, 1) . (count($words) > 1 ? mb_substr(end($words), 0, 1) : ''));
    }

    /** Membres visibles : direction d'abord, puis ordre d'affichage. */
    public static function visibleList()
    {
        return static::where('is_visible', true)->orderByDesc('is_leader')->orderBy('sort_order')->orderBy('name')->get();
    }
}
