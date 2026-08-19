<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Support\ManagesPublicFiles;

class ProfileController extends Controller
{
    use ManagesPublicFiles;

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $oldTtd = $user->ttd;
        $oldSignaturePath = $user->signature_path;

        if ($request->input('ttd_action') === 'delete') {
            if ($user->ttd) {
                $this->deletePublicFile($user->ttd);
            }

            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $validated['ttd'] = null;
            $validated['signature_path'] = null;
        } elseif ($request->hasFile('ttd')) {
            $validated['ttd'] = $this->storePublicUploadedFile($request->file('ttd'), 'ttd');
            // Also store under the 'public' disk as signature_path — this is the field
            // LocController::approve() reads (via User::signature_url) to stamp documents,
            // so a self-uploaded signature needs to land here too, not just in ttd.
            $validated['signature_path'] = $request->file('ttd')->store('signatures', 'public');
        } else {
            unset($validated['ttd']);
        }

        $user->fill($validated);

        $user->save();

        if ($request->hasFile('ttd') && $oldSignaturePath) {
            Storage::disk('public')->delete($oldSignaturePath);
        }

        if ($request->hasFile('ttd') && $oldTtd && $oldTtd !== $validated['ttd']) {
            $this->deletePublicFile($oldTtd);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->deletePublicFile($user->ttd);
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
