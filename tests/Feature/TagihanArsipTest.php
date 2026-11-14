<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TagihanArsipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Layout memakai @vite; di tes tidak ada hasil build.
        $this->withoutVite();
    }

    private function pemilik(string $nama = 'Andi Pratama'): User
    {
        return User::factory()->create(['name' => $nama, 'role' => User::ROLE_PEMILIK]);
    }

    private function penghuniDenganKamar(User $pemilik, string $nama, string $nomor): User
    {
        $penghuni = User::factory()->create(['name' => $nama, 'role' => User::ROLE_PENGHUNI]);

        $kamar = new Kamar(['nomor_kamar' => $nomor, 'tipe' => 'Standard', 'harga' => 900000, 'penghuni_id' => $penghuni->id]);
        $kamar->pemilik_id = $pemilik->id;
        $kamar->save();

        return $penghuni;
    }

    private function buatTagihan(User $pemilik, User $penghuni, string $periodeBulan = '2026-10'): Tagihan
    {
        $this->actingAs($pemilik)->post(route('pemilik.tagihan.store'), [
            'penghuni_id' => $penghuni->id,
            'periode_bulan' => $periodeBulan,
            'jumlah' => '900000',
            'jatuh_tempo' => $periodeBulan.'-10',
        ])->assertRedirect(route('pemilik.tagihan.index'));

        // Cari berdasarkan penghuni + periode (kunci unik), bukan "tagihan terakhir".
        return Tagihan::where('penghuni_id', $penghuni->id)
            ->where('periode', Tagihan::periodeDariBulan($periodeBulan))
            ->firstOrFail();
    }

    private function payload(User $penghuni, string $jumlah = '950000'): array
    {
        return [
            'penghuni_id' => $penghuni->id,
            'periode_bulan' => '2026-10',
            'jumlah' => $jumlah,
            'jatuh_tempo' => '2026-10-10',
            'status' => 'belum_dibayar',
        ];
    }

    public function test_skema_tagihan_memakai_foreign_key_nullable_dan_kolom_snapshot(): void
    {
        // RefreshDatabase menjalankan migration proyek, jadi ini memeriksa skema hasil migration yang sebenarnya.
        $this->assertTrue(Schema::hasColumns('tagihan', ['nama_penghuni', 'nama_pemilik']));

        $kolom = collect(Schema::getColumns('tagihan'))->keyBy('name');

        $this->assertTrue($kolom['penghuni_id']['nullable']);
        $this->assertTrue($kolom['pemilik_id']['nullable']);
        $this->assertFalse($kolom['nama_penghuni']['nullable']);
        $this->assertFalse($kolom['nama_pemilik']['nullable']);
    }

    public function test_snapshot_nama_tersimpan_saat_tagihan_dibuat_dan_tidak_ikut_berubah_saat_profil_berubah(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = $this->penghuniDenganKamar($pemilik, 'Nikita Arzetty', 'A-03');
        $tagihan = $this->buatTagihan($pemilik, $penghuni);

        $this->assertSame('Nikita Arzetty', $tagihan->nama_penghuni);
        $this->assertSame('Andi Pratama', $tagihan->nama_pemilik);

        $pemilik->update(['name' => 'Nama Baru Pemilik']);
        $penghuni->update(['name' => 'Nama Baru Penghuni']);

        $tagihan->refresh();
        $this->assertSame('Nikita Arzetty', $tagihan->nama_penghuni_tampil);
        $this->assertSame('Andi Pratama', $tagihan->nama_pemilik_tampil);

        $this->actingAs($pemilik)->get(route('pemilik.tagihan.index'))
            ->assertOk()
            ->assertSee('Nikita Arzetty')
            ->assertDontSee('Nama Baru Penghuni');
    }

    public function test_snapshot_penghuni_diperbarui_saat_penghuni_diganti_dan_snapshot_pemilik_tetap(): void
    {
        $pemilik = $this->pemilik();
        $lama = $this->penghuniDenganKamar($pemilik, 'Nikita Arzetty', 'A-03');
        $baru = $this->penghuniDenganKamar($pemilik, 'Salsa Putri', 'A-01');
        $tagihan = $this->buatTagihan($pemilik, $lama);

        $pemilik->update(['name' => 'Nama Baru Pemilik']);

        $this->actingAs($pemilik)
            ->patch(route('pemilik.tagihan.update', $tagihan), $this->payload($baru))
            ->assertRedirect(route('pemilik.tagihan.index'));

        $tagihan->refresh();
        $this->assertSame($baru->id, (int) $tagihan->penghuni_id);
        $this->assertSame('Salsa Putri', $tagihan->nama_penghuni);
        $this->assertSame('Andi Pratama', $tagihan->nama_pemilik);
    }

    public function test_riwayat_tetap_ada_saat_penghuni_dihapus_dan_menjadi_arsip_hanya_baca(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = $this->penghuniDenganKamar($pemilik, 'Nikita Arzetty', 'A-03');
        $tagihan = $this->buatTagihan($pemilik, $penghuni);

        $penghuni->delete();
        $tagihan->refresh();

        $this->assertNull($tagihan->penghuni_id);
        $this->assertSame('Nikita Arzetty', $tagihan->nama_penghuni);
        $this->assertTrue($tagihan->isArsip());

        $this->actingAs($pemilik)->get(route('pemilik.tagihan.index'))
            ->assertOk()
            ->assertSee('Nikita Arzetty')
            ->assertSee('hanya-baca')
            ->assertDontSee(route('pemilik.tagihan.edit', $tagihan));

        $pengganti = $this->penghuniDenganKamar($pemilik, 'Salsa Putri', 'A-01');

        $this->get(route('pemilik.tagihan.edit', $tagihan))->assertNotFound();
        $this->patch(route('pemilik.tagihan.update', $tagihan), $this->payload($pengganti))->assertNotFound();
        $this->delete(route('pemilik.tagihan.destroy', $tagihan))->assertNotFound();

        $tagihan->refresh();
        $this->assertNull($tagihan->penghuni_id);
        $this->assertSame('Nikita Arzetty', $tagihan->nama_penghuni);
    }

    public function test_riwayat_tetap_ada_saat_pemilik_dihapus_dan_pemilik_pengganti_hanya_bisa_melihat(): void
    {
        $lama = $this->pemilik('Andi Pratama');
        $penghuni = $this->penghuniDenganKamar($lama, 'Nikita Arzetty', 'A-03');
        $tagihan = $this->buatTagihan($lama, $penghuni);

        $lama->delete();
        $tagihan->refresh();

        $this->assertNull($tagihan->pemilik_id);
        $this->assertSame('Andi Pratama', $tagihan->nama_pemilik);
        $this->assertTrue($tagihan->isArsip());

        $pengganti = $this->pemilik('Budi Pengganti');
        $penghuniBaru = $this->penghuniDenganKamar($pengganti, 'Salsa Putri', 'A-01');

        $this->actingAs($pengganti)->get(route('pemilik.tagihan.index'))
            ->assertOk()
            ->assertSee('Nikita Arzetty')
            ->assertSee('Andi Pratama')
            ->assertSee('hanya-baca')
            ->assertDontSee(route('pemilik.tagihan.edit', $tagihan));

        $this->get(route('pemilik.tagihan.edit', $tagihan))->assertNotFound();
        $this->patch(route('pemilik.tagihan.update', $tagihan), $this->payload($penghuniBaru))->assertNotFound();
        $this->delete(route('pemilik.tagihan.destroy', $tagihan))->assertNotFound();

        $tagihan->refresh();
        $this->assertNull($tagihan->pemilik_id);
        $this->assertSame('900000.00', $tagihan->jumlah);
    }

    public function test_pemilik_tidak_bisa_melihat_atau_mengelola_tagihan_aktif_milik_pemilik_lain(): void
    {
        $a = $this->pemilik('Pemilik A');
        $b = $this->pemilik('Pemilik B');
        $penghuniB = $this->penghuniDenganKamar($b, 'Penghuni B', 'B-01');
        $tagihanB = $this->buatTagihan($b, $penghuniB);

        $this->actingAs($a)->get(route('pemilik.tagihan.index'))
            ->assertOk()
            ->assertDontSee('Penghuni B');

        $this->get(route('pemilik.tagihan.edit', $tagihanB))->assertNotFound();
        $this->delete(route('pemilik.tagihan.destroy', $tagihanB))->assertNotFound();
        $this->assertDatabaseHas('tagihan', ['id' => $tagihanB->id]);
    }

    public function test_tagihan_aktif_tetap_bisa_diedit_dan_dihapus_pemiliknya(): void
    {
        $pemilik = $this->pemilik();
        $penghuni = $this->penghuniDenganKamar($pemilik, 'Nikita Arzetty', 'A-03');
        $tagihan = $this->buatTagihan($pemilik, $penghuni);

        $this->actingAs($pemilik)->get(route('pemilik.tagihan.edit', $tagihan))->assertOk();

        $this->patch(route('pemilik.tagihan.update', $tagihan), $this->payload($penghuni, '1000000'))
            ->assertRedirect(route('pemilik.tagihan.index'));
        $this->assertSame('1000000.00', $tagihan->fresh()->jumlah);

        $this->delete(route('pemilik.tagihan.destroy', $tagihan))->assertRedirect(route('pemilik.tagihan.index'));
        $this->assertDatabaseMissing('tagihan', ['id' => $tagihan->id]);
    }
}
