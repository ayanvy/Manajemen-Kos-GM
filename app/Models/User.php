<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'phone', 'profile_photo_path',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_PEMILIK = 'pemilik';
    public const ROLE_PENGHUNI = 'penghuni';

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Default role untuk objek User baru.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => self::ROLE_PENGHUNI,
    ];

    /** Kamar yang sudah dikonfirmasi pemilik untuk penghuni ini (null jika belum ada). */
    public function kamar(): HasOne
    {
        return $this->hasOne(Kamar::class, 'penghuni_id');
    }

    /** Tagihan yang diterima penghuni ini. */
    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'penghuni_id');
    }

    public function isPemilik(): bool
    {
        return $this->role === self::ROLE_PEMILIK;
    }

    public function isPenghuni(): bool
    {
        return $this->role === self::ROLE_PENGHUNI;
    }

    /**
     * Ambil atribut yang perlu diubah tipe datanya..
     *
     * @return array<string, string>
     */
    protected static function booted(): void
{
    static::deleting(function (User $user) {
        // Kosongkan kamar yang terhubung dengan penghuni.
        Kamar::where('penghuni_id', $user->id)
            ->get()
            ->each(fn (Kamar $kamar) => $kamar->update([
                'penghuni_id' => null,
            ]));

        // Hapus foto profil penghuni jika tersedia.
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Bersihkan session jika aplikasi menggunakan database.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
    });
}
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

