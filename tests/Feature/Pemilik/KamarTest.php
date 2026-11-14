<?php

namespace Tests\Feature\Pemilik;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarTest extends TestCase
{
    use RefreshDatabase;

    private function pemilik(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_PEMILIK])->save();

        return $user;
    }

    private function dataValid(array $override = []): array
    {
        return array_merge([
            'nomor_kamar' => 'A-01',
            'tipe' => 'Standard',
            'harga' => '900000',
            'fasilitas' => 'Kasur, lemari, Wi-Fi',
            'penghuni_id' => null,
        ], $override);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('pemilik.kamar.index'))->assertRedirect(route('login'));
    }

    public function test_penghuni_tidak_boleh_mengakses(): void
    {
        $penghuni = User::factory()->create();

        $this->actingAs($penghuni)->get(route('pemilik.kamar.index'))->assertForbidden();
        $this->actingAs($penghuni)->post(route('pemilik.kamar.store'), $this->dataValid())->assertForbidden();

        $this->assertDatabaseCount('kamar', 0);
    }

    public function test_pemilik_hanya_melihat_kamar_miliknya(): void
    {
        $pemilik = $this->pemilik();
        $lain = $this->pemilik();

        Kamar::factory()->create(['pemilik_id' => $pemilik->id, 'nomor_kamar' => 'A-01']);
        Kamar::factory()->create(['pemilik_id' => $lain->id, 'nomor_kamar' => 'Z-99']);

        $this->actingAs($pemilik)
            ->get(route('pemilik.kamar.index'))
            ->assertOk()
            ->assertSee('A-01')
            ->assertDontSee('Z-99');
    }

    public function test_pemilik_bisa_menambah_kamar(): void
    {
        $pemilik = $this->pemilik();

        $this->actingAs($pemilik)
            ->post(route('pemilik.kamar.store'), $this->dataValid(['harga' => '1.000.000']))
            ->assertRedirect(route('pemilik.kamar.index'));

        $this->assertDatabaseHas('kamar', [
            'pemilik_id' => $pemilik->id,
            'nomor_kamar' => 'A-01',
            'tipe' => 'Standard',
            'harga' => 1000000,
            'status' => 'tersedia',
        ]);
    }

    public function test_pemilik_id_tidak_bisa_dipalsukan_lewat_form(): void
    {
        $pemilik = $this->pemilik();
        $lain = $this->pemilik();

        $this->actingAs($pemilik)
            ->post(route('pemilik.kamar.store'), $this->dataValid(['pemilik_id' => $lain->id]));

        $this->assertDatabaseHas('kamar', ['nomor_kamar' => 'A-01', 'pemilik_id' => $pemilik->id]);
    }

    public function test_validasi_data_kamar(): void
    {
        $pemilik = $this->pemilik();

        $this->actingAs($pemilik)
            ->post(route('pemilik.kamar.store'), [
                'nomor_kamar' => '',
                'tipe' => '',
                'harga' => 'abc',
                'penghuni_id' => 99999,
            ])
            ->assertSessionHasErrors(['nomor_kamar', 'tipe', 'harga', 'penghuni_id']);

        $this->assertDatabaseCount('kamar', 0);
    }

    public function test_nomor_kamar_tidak_boleh_kembar_untuk_pemilik_yang_sama(): void
    {
        $pemilik = $this->pemilik();
        Kamar::factory()->create(['pemilik_id' => $pemilik->id, 'nomor_kamar' => 'A-01']);

        $this->actingAs($pemilik)
            ->post(route('pemilik.kamar.store'), $this->dataValid())
            ->assertSessionHasErrors('nomor_kamar');

        $this->assertDatabaseCount('kamar', 1);
    }

    public function test_nomor_kamar_boleh_sama_dengan_pemilik_lain(): void
    {
        $lain = $this->pemilik();
        Kamar::factory()->create(['pemilik_id' => $lain->id, 'nomor_kamar' => 'A-01']);

        $this->actingAs($this->pemilik())
            ->post(route('pemilik.kamar.store'), $this->dataValid())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('kamar', 2);
    }

    public function test_pemilik_bisa_mengubah_kamar_tanpa_bentrok_nomor_sendiri(): void
    {
        $pemilik = $this->pemilik();
        $kamar = Kamar::factory()->create(['pemilik_id' => $pemilik->id, 'nomor_kamar' => 'A-01']);
        $penghuni = User::factory()->create();

        $this->actingAs($pemilik)
            ->patch(route('pemilik.kamar.update', $kamar), $this->dataValid([
                'tipe' => 'Deluxe',
                'harga' => '1000000',
                'penghuni_id' => $penghuni->id,
            ]))
            ->assertRedirect(route('pemilik.kamar.index'));

        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'tipe' => 'Deluxe',
            'harga' => 1000000,
            'penghuni_id' => $penghuni->id,
            'status' => 'terisi',
        ]);
    }

    public function test_pemilik_bisa_mengosongkan_kamar(): void
    {
        $pemilik = $this->pemilik();
        $kamar = Kamar::factory()->terisi()->create(['pemilik_id' => $pemilik->id]);

        $this->actingAs($pemilik)
            ->patch(route('pemilik.kamar.update', $kamar), $this->dataValid(['nomor_kamar' => $kamar->nomor_kamar]))
            ->assertRedirect(route('pemilik.kamar.index'));

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'penghuni_id' => null, 'status' => 'tersedia']);
    }

    public function test_satu_penghuni_tidak_boleh_menempati_dua_kamar(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = User::factory()->create();
        Kamar::factory()->create(['pemilik_id' => $pemilik->id, 'penghuni_id' => $penghuni->id]);

        $this->actingAs($pemilik)
            ->post(route('pemilik.kamar.store'), $this->dataValid(['nomor_kamar' => 'B-01', 'penghuni_id' => $penghuni->id]))
            ->assertSessionHasErrors('penghuni_id');
    }

    public function test_pemilik_tidak_bisa_memilih_pemilik_lain_sebagai_penghuni(): void
    {
        $pemilik = $this->pemilik();

        $this->actingAs($pemilik)
            ->post(route('pemilik.kamar.store'), $this->dataValid(['penghuni_id' => $this->pemilik()->id]))
            ->assertSessionHasErrors('penghuni_id');
    }

    public function test_dashboard_pemilik_menampilkan_jumlah_kamar_terisi_dan_tersedia(): void
    {
        $pemilik = $this->pemilik();
        Kamar::factory()->terisi()->count(2)->create(['pemilik_id' => $pemilik->id]);
        Kamar::factory()->count(3)->create(['pemilik_id' => $pemilik->id]);

        $this->actingAs($pemilik)
            ->get(route('pemilik.dashboard'))
            ->assertOk()
            ->assertSee('dari 5 kamar')
            ->assertSee(route('pemilik.kamar.index'));
    }

    public function test_penghuni_belum_bisa_lihat_kamar_sebelum_dikonfirmasi(): void
    {
        $penghuni = User::factory()->create();
        Kamar::factory()->create(['nomor_kamar' => 'A-77']);

        $this->actingAs($penghuni)
            ->get(route('penghuni.kamar'))
            ->assertOk()
            ->assertSee('Kamar belum dikonfirmasi')
            ->assertDontSee('A-77');
    }

    public function test_penghuni_bisa_lihat_kamar_setelah_dikonfirmasi(): void
    {
        $penghuni = User::factory()->create();
        Kamar::factory()->create(['nomor_kamar' => 'A-03', 'penghuni_id' => $penghuni->id]);
        Kamar::factory()->create(['nomor_kamar' => 'Z-99']);

        $this->actingAs($penghuni)
            ->get(route('penghuni.kamar'))
            ->assertOk()
            ->assertSee('A-03')
            ->assertDontSee('Z-99');
    }

    public function test_pemilik_tidak_bisa_mengubah_atau_menghapus_kamar_pemilik_lain(): void
    {
        $lain = $this->pemilik();
        $kamar = Kamar::factory()->create(['pemilik_id' => $lain->id]);
        $pemilik = $this->pemilik();

        $this->actingAs($pemilik)->get(route('pemilik.kamar.edit', $kamar))->assertNotFound();
        $this->actingAs($pemilik)->patch(route('pemilik.kamar.update', $kamar), $this->dataValid())->assertNotFound();
        $this->actingAs($pemilik)->delete(route('pemilik.kamar.destroy', $kamar))->assertNotFound();

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }

    public function test_pemilik_bisa_menghapus_kamar_tersedia(): void
    {
        $pemilik = $this->pemilik();
        $kamar = Kamar::factory()->create(['pemilik_id' => $pemilik->id]);

        $this->actingAs($pemilik)
            ->delete(route('pemilik.kamar.destroy', $kamar))
            ->assertRedirect(route('pemilik.kamar.index'));

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
    }

    public function test_kamar_terisi_tidak_bisa_dihapus(): void
    {
        $pemilik = $this->pemilik();
        $kamar = Kamar::factory()->terisi()->create(['pemilik_id' => $pemilik->id]);

        $this->actingAs($pemilik)
            ->delete(route('pemilik.kamar.destroy', $kamar))
            ->assertRedirect(route('pemilik.kamar.index'))
            ->assertSessionHas('status', 'kamar-terisi');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }
}
