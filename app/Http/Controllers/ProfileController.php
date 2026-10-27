<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function show(): View
    {
        return view('profile.show', [
            'user' => request()->user(),
        ]);
    }

    public function edit(): View
    {
        return view('profile.edit', [
            'user' => request()->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $attributes = [
            'name' => $request->validated('name'),
        ];

        if ($request->filled('password')) {
            $attributes['password'] = $request->validated('password');
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $attributes['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $this->users->update($user, $attributes);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profile updated successfully.');
    }
}
