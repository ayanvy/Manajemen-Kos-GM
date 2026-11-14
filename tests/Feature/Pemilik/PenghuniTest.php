<?php

namespace Tests\Feature\Pemilik;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenghuniTest extends TestCase
{
    use RefreshDatabase;

    private function pemilik(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_PEMILIK])->save();

        return $user;
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('pemilik.penghuni.index'))->assertRedirect(route('login'));
    }

    public function test_penghuni_tidak_boleh_mengakses(): void
    {
        $penghuni = User::factory()->create();
        $lain = User::factory()->create();

        $this->actingAs($penghuni)->get(route('pemilik.penghuni.index'))->assertForbidden();
        $this->actingAs($penghuni)->delete(route('pemilik.penghuni.destroy', $lain))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $lain->id]);
    }

    public function test_akun_baru_dari_register_otomatis_muncul_di_daftar_pemilik(): void
    {
        $this->post(route('register'), [
            'name' => 'Nikita Arzetty',
            'email' => 'nikita@kosgm.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        auth()->logout();

        $this->actingAs($this->pemilik())
            ->get(route('pemilik.penghuni.index'))
            ->assertOk()
            ->assertSee('Nikita Arzetty')
            ->assertSee('nikita@kosgm.id');
    }

    public function test_daftar_hanya_berisi_penghuni_bukan_pemilik(): void
    {
        $pemilik = $this->pemilik();
        $lain = $this->pemilik();
        $lain->forceFill(['name' => 'Pemilik Lain'])->save();

        $this->actingAs($pemilik)
            ->get(route('pemilik.penghuni.index'))
            ->assertOk()
            ->assertDontSee('Pemilik Lain');
    }

    public function test_pemilik_bisa_menghapus_penghuni_dan_kamarnya_jadi_tersedia(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = User::factory()->create();
        $kamar = Kamar::factory()->create([
            'pemilik_id' => $pemilik->id,
            'penghuni_id' => $penghuni->id,
        ]);

        $this->assertSame(Kamar::STATUS_TERISI, $kamar->fresh()->status);

        $this->actingAs($pemilik)
            ->delete(route('pemilik.penghuni.destroy', $penghuni))
            ->assertRedirect(route('pemilik.penghuni.index'));

        $this->assertDatabaseMissing('users', ['id' => $penghuni->id]);
        $this->assertNull($kamar->fresh()->penghuni_id);
        $this->assertSame(Kamar::STATUS_TERSEDIA, $kamar->fresh()->status);
    }

    public function test_penghuni_yang_dihapus_tidak_bisa_login_lagi(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = User::factory()->create(['email' => 'hapus@kosgm.id']);

        $this->actingAs($pemilik)->delete(route('pemilik.penghuni.destroy', $penghuni));
        auth()->logout();

        $this->post(route('login'), [
            'email' => 'hapus@kosgm.id',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_pemilik_tidak_bisa_menghapus_sesama_pemilik(): void
    {
        $pemilik = $this->pemilik();
        $lain = $this->pemilik();

        $this->actingAs($pemilik)
            ->delete(route('pemilik.penghuni.destroy', $lain))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $lain->id]);
    }

    public function test_pemilik_tidak_bisa_tambah_atau_edit_penghuni(): void
{
    $pemilik = $this->pemilik();
    $penghuni = User::factory()->create();

    $this->actingAs($pemilik);

    $this->post('/pemilik/penghuni', ['name' => 'Baru'])
        ->assertStatus(405);

    $this->get('/pemilik/penghuni/create')
        ->assertStatus(405);

    $this->get("/pemilik/penghuni/{$penghuni->id}/edit")
        ->assertNotFound();

    $this->put("/pemilik/penghuni/{$penghuni->id}", ['name' => 'Ubah'])
        ->assertStatus(405);
}

    public function test_penghuni_hapus_akun_sendiri_hilang_dari_daftar_pemilik(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = User::factory()->create(['name' => 'Salsa Putri']);
        $kamar = Kamar::factory()->create([
            'pemilik_id' => $pemilik->id,
            'penghuni_id' => $penghuni->id,
        ]);

        $this->actingAs($penghuni)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $penghuni->id]);
        $this->assertSame(Kamar::STATUS_TERSEDIA, $kamar->fresh()->status);

        auth()->logout();

        $this->actingAs($pemilik)
            ->get(route('pemilik.penghuni.index'))
            ->assertOk()
            ->assertDontSee('Salsa Putri');
    }
}
