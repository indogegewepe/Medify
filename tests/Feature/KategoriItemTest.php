<?php

namespace Tests\Feature;

use App\Models\KategoriItem;
use App\Models\MasterItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategori_dapat_dibuat_diperbarui_dan_dihapus(): void
    {
        $this->post(route('kategori-items.store'), [
            'nama' => 'Obat Bebas',
        ])->assertRedirect(route('kategori-items.index'))
            ->assertSessionHas('success');

        $kategori = KategoriItem::where('nama', 'Obat Bebas')->firstOrFail();

        $this->put(route('kategori-items.update', $kategori), [
            'nama' => 'Obat Resep',
        ])->assertRedirect(route('kategori-items.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_items', [
            'id' => $kategori->id,
            'nama' => 'Obat Resep',
        ]);

        $this->delete(route('kategori-items.destroy', $kategori))
            ->assertRedirect(route('kategori-items.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kategori_items', ['id' => $kategori->id]);
    }

    public function test_nama_kategori_wajib_dan_unik(): void
    {
        $kategoriPertama = KategoriItem::create(['nama' => 'Alat Kesehatan']);
        $kategoriKedua = KategoriItem::create(['nama' => 'Obat']);

        $this->post(route('kategori-items.store'), ['nama' => ''])
            ->assertSessionHasErrors('nama');

        $this->post(route('kategori-items.store'), ['nama' => $kategoriPertama->nama])
            ->assertSessionHasErrors('nama');

        $this->put(route('kategori-items.update', $kategoriKedua), [
            'nama' => $kategoriPertama->nama,
        ])->assertSessionHasErrors('nama');
    }

    public function test_halaman_crud_kategori_dapat_dirender(): void
    {
        $kategori = KategoriItem::create(['nama' => 'Perlengkapan Medis']);

        $this->get(route('kategori-items.index'))
            ->assertOk()
            ->assertSee('Daftar Kategori Items')
            ->assertSee('Perlengkapan Medis');

        $this->get(route('kategori-items.create'))
            ->assertOk()
            ->assertSee('Buat Kategori Baru');

        $this->get(route('kategori-items.edit', $kategori))
            ->assertOk()
            ->assertSee('Edit Kategori')
            ->assertSee('Perlengkapan Medis');
    }

    public function test_kategori_yang_digunakan_item_aktif_atau_soft_deleted_tidak_dapat_dihapus(): void
    {
        $kategoriAktif = KategoriItem::create(['nama' => 'Aktif']);
        $kategoriTerhapus = KategoriItem::create(['nama' => 'Terhapus']);

        $this->createMasterItem('00001', $kategoriAktif->id);
        $itemTerhapus = $this->createMasterItem('00002', $kategoriTerhapus->id);
        $itemTerhapus->delete();

        $this->delete(route('kategori-items.destroy', $kategoriAktif))
            ->assertSessionHas('error');

        $this->delete(route('kategori-items.destroy', $kategoriTerhapus))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_items', ['id' => $kategoriAktif->id]);
        $this->assertDatabaseHas('kategori_items', ['id' => $kategoriTerhapus->id]);
    }

    public function test_master_item_memerlukan_kategori_valid_saat_dibuat_dan_diedit(): void
    {
        $kategoriAwal = KategoriItem::create(['nama' => 'Kategori Awal']);
        $kategoriBaru = KategoriItem::create(['nama' => 'Kategori Baru']);

        $this->post('/master-items/form/new', $this->masterItemPayload(null))
            ->assertSessionHasErrors('kategori_id');

        $this->post('/master-items/form/new', $this->masterItemPayload(999999))
            ->assertSessionHasErrors('kategori_id');

        $this->post('/master-items/form/new', $this->masterItemPayload($kategoriAwal->id))
            ->assertRedirect('master-items');

        $item = MasterItem::where('nama', 'Paracetamol')->firstOrFail();
        $this->assertSame($kategoriAwal->id, $item->kategori_id);

        $this->post('/master-items/form/edit/' . $item->id, $this->masterItemPayload($kategoriBaru->id))
            ->assertRedirect('master-items');

        $this->assertSame($kategoriBaru->id, $item->fresh()->kategori_id);
    }

    public function test_pencarian_dapat_difilter_berdasarkan_kategori_dan_menampilkan_item_lama(): void
    {
        $kategoriSatu = KategoriItem::create(['nama' => 'Kategori Satu']);
        $kategoriDua = KategoriItem::create(['nama' => 'Kategori Dua']);

        $this->createMasterItem('00001', $kategoriSatu->id);
        $this->createMasterItem('00002', $kategoriDua->id);
        $this->createMasterItem('00003', null);

        $this->getJson('/master-items/search?kategori_id=' . $kategoriSatu->id)
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode', '00001')
            ->assertJsonPath('data.0.kategori.id', $kategoriSatu->id)
            ->assertJsonPath('data.0.kategori.nama', 'Kategori Satu');

        $this->getJson('/master-items/search')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.2.kode', '00003')
            ->assertJsonPath('data.2.kategori', null);
    }

    public function test_halaman_master_item_menampilkan_kontrol_dan_nilai_kategori(): void
    {
        $kategori = KategoriItem::create(['nama' => 'Suplemen']);
        $item = $this->createMasterItem('00001', $kategori->id);

        $this->get('/master-items')
            ->assertOk()
            ->assertSee(route('kategori-items.index'))
            ->assertSee('filter-kategori')
            ->assertSee('Semua Kategori');

        $this->get('/master-items/form/new')
            ->assertOk()
            ->assertSee('kategori_id')
            ->assertSee('Suplemen');

        $this->get('/master-items/view/' . $item->kode)
            ->assertOk()
            ->assertSee('Kategori')
            ->assertSee('Suplemen')
            ->assertSee(route('master-items.print', $item->kode))
            ->assertSee('Print');
    }

    public function test_detail_master_item_dapat_dicetak_sebagai_pdf(): void
    {
        $kategori = KategoriItem::create(['nama' => 'Obat Bebas']);
        $item = $this->createMasterItem('00001', $kategori->id);
        $printedAt = now()->setDateTime(2026, 10, 6, 14, 30, 45);

        $html = view('master_items.single.print', compact('item', 'printedAt'))->render();

        foreach (['Nama Kategori', 'Kode Kategori', 'Nama', 'Harga Beli', 'Laba', 'Harga Jual', 'Supplier', 'Jenis'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringContainsString('Obat Bebas', $html);
        $this->assertStringContainsString('Dicetak pada 06-10-2026 14:30:45', $html);

        $response = $this->get(route('master-items.print', $item->kode));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=master-item-00001.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    private function createMasterItem(string $kode, ?int $kategoriId): MasterItem
    {
        return MasterItem::forceCreate([
            'kode' => $kode,
            'nama' => 'Item ' . $kode,
            'harga_beli' => 10000,
            'laba' => 10,
            'supplier' => 'Tokopaedi',
            'jenis' => 'Obat',
            'kategori_id' => $kategoriId,
        ]);
    }

    private function masterItemPayload(?int $kategoriId): array
    {
        return [
            'nama' => 'Paracetamol',
            'harga_beli' => 12000,
            'laba' => 15,
            'supplier' => 'Tokopaedi',
            'jenis' => 'Obat',
            'kategori_id' => $kategoriId,
        ];
    }
}
