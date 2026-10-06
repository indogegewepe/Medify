<?php

namespace Tests\Feature;

use App\Exports\MasterItemsExport;
use App\Models\KategoriItem;
use App\Models\MasterItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterItemsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_master_items_menampilkan_tombol_export_excel(): void
    {
        $this->get('/master-items')
            ->assertOk()
            ->assertSee(route('master-items.export'))
            ->assertSee('Export to Excel');
    }

    public function test_endpoint_export_mengunduh_file_xlsx(): void
    {
        $kategori = KategoriItem::create(['nama' => 'Obat Bebas']);
        $this->createMasterItem('00001', $kategori->id, 'Paracetamol', 10000, 10);

        $this->get(route('master-items.export'))
            ->assertOk()
            ->assertDownload('master-items.xlsx')
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
    }

    public function test_export_memetakan_seluruh_item_aktif_dan_mengabaikan_item_terhapus(): void
    {
        $kategori = KategoriItem::create(['nama' => 'Obat Bebas']);
        $itemPertama = $this->createMasterItem('00001', $kategori->id, 'Paracetamol', 10000, 10);
        $itemTanpaKategori = $this->createMasterItem('00002', null, 'Masker', 7500, 20);
        $itemTerhapus = $this->createMasterItem('00003', $kategori->id, 'Terhapus', 5000, 5);
        $itemTerhapus->delete();

        $export = new MasterItemsExport;
        $items = $export->query()->get();

        $this->assertSame($this->expectedHeadings(), $export->headings());
        $this->assertCount(2, $items);
        $this->assertTrue($items->contains($itemPertama));
        $this->assertTrue($items->contains($itemTanpaKategori));
        $this->assertFalse($items->contains($itemTerhapus));
        $this->assertSame([
            1,
            'Obat Bebas',
            'Paracetamol',
            'Supplier A',
            10000,
            10,
            11000,
        ], $export->map($items[0]));
        $this->assertSame([
            2,
            '-',
            'Masker',
            'Supplier A',
            7500,
            20,
            9000,
        ], $export->map($items[1]));
    }

    private function createMasterItem(
        string $kode,
        ?int $kategoriId,
        string $nama,
        int $hargaBeli,
        int $laba
    ): MasterItem {
        return MasterItem::forceCreate([
            'kode' => $kode,
            'nama' => $nama,
            'harga_beli' => $hargaBeli,
            'laba' => $laba,
            'supplier' => 'Supplier A',
            'jenis' => 'Obat',
            'kategori_id' => $kategoriId,
        ]);
    }

    private function expectedHeadings(): array
    {
        return [
            'No',
            'Nama Kategori',
            'Nama Items',
            'Nama Supplier',
            'Harga',
            'Laba',
            'Harga Jual',
        ];
    }
}
