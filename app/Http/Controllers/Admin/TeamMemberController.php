<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::orderByDesc('is_leader')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.team.index', compact('members'));
    }

    public function store(Request $request)
    {
        TeamMember::create($this->data($request));

        return back()->with('success', 'Membre ajouté.');
    }

    public function update(Request $request, TeamMember $member)
    {
        $data = $this->data($request, $member);
        $member->update($data);

        return back()->with('success', 'Membre mis à jour.');
    }

    public function destroy(TeamMember $member)
    {
        if ($member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
        }
        $member->delete();

        return back()->with('success', 'Membre supprimé.');
    }

    private function data(Request $request, ?TeamMember $member = null): array
    {
        $v = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'title'      => ['required', 'string', 'max:120'],
            'bio'        => ['nullable', 'string', 'max:1500'],
            'photo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $data = [
            'name'       => $v['name'],
            'title'      => $v['title'],
            'bio'        => $v['bio'] ?? null,
            'sort_order' => (int) ($v['sort_order'] ?? 0),
            'is_leader'  => $request->boolean('is_leader'),
            'is_visible' => $request->boolean('is_visible'),
        ];

        if ($request->hasFile('photo')) {
            if ($member?->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('team', 'public');
        } elseif ($request->boolean('remove_photo') && $member?->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
            $data['photo_path'] = null;
        }

        return $data;
    }
}
