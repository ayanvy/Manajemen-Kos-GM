<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tampilkan formulir profil pengguna.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Perbarui informasi profil pengguna.
     */
    
    public function update( ProfileUpdateRequest $request, CloudinaryService $cloudinary ): RedirectResponse {
        $user = $request->user();

        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_photo')) {
            $result = $cloudinary->uploadImage(
                $request->file('profile_photo')->getRealPath(),
                'sistem-informasi-kos/profile'
            );

            $fotoLama = $user->profile_photo_path;
            $publicIdLama = $user->profile_photo_public_id;

            $user->profile_photo_path = $result['secure_url'];
            $user->profile_photo_public_id = $result['public_id'];

            $user->save();

            if ($publicIdLama) {
                try {
                    $cloudinary->deleteImage($publicIdLama);
                } catch (\Throwable $e) {
                    report($e);
                }
            } elseif ($fotoLama && ! str_starts_with($fotoLama, 'http')) {
                Storage::disk('public')->delete($fotoLama);
            }
        } else {
            $user->save();
        }

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }


    public function destroyPhoto( Request $request, CloudinaryService $cloudinary ): RedirectResponse {
        $user = $request->user();

        if ($user->profile_photo_public_id) {
            $cloudinary->deleteImage($user->profile_photo_public_id);
        } elseif (
            $user->profile_photo_path &&
            ! str_starts_with($user->profile_photo_path, 'http')
        ) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = null;
        $user->profile_photo_public_id = null;
        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-photo-deleted');
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
