<?php

namespace App\Http\Controllers;

use App\Exports\MasterItemsExport;
use App\Models\KategoriItem;
use App\Models\MasterItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MasterItemsController extends Controller
{
    public function index()
    {
        return view('master_items.index.index', [
            'kategoriItems' => KategoriItem::orderBy('nama')->get(),
        ]);
    }

    public function export()
    {
        return Excel::download(new MasterItemsExport, 'master-items.xlsx');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $hargamin = $request->hargamin;
        $hargamax = $request->hargamax;
        $kategoriId = $request->kategori_id;

        $data_search = MasterItem::with('kategori:id,nama');

        if (!empty($kode)) $data_search = $data_search->where('kode', $kode);
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');
        if ($request->filled('hargamin')) $data_search = $data_search->where('harga_beli', '>=', $hargamin);
        if ($request->filled('hargamax')) $data_search = $data_search->where('harga_beli', '<=', $hargamax);
        if ($request->filled('kategori_id')) $data_search = $data_search->where('kategori_id', $kategoriId);

        $data_search = $data_search
            ->select('kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier', 'kategori_id')
            ->orderBy('id')
            ->get()
            ->map(function (MasterItem $item) {
                return [
                    'kode' => $item->kode,
                    'nama' => $item->nama,
                    'kategori' => $item->kategori ? [
                        'id' => $item->kategori->id,
                        'nama' => $item->kategori->nama,
                    ] : null,
                    'jenis' => $item->jenis,
                    'harga_beli' => $item->harga_beli,
                    'laba' => $item->laba,
                    'supplier' => $item->supplier,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = [];
        } else {
            $item = MasterItem::findOrFail($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        $data['kategoriItems'] = KategoriItem::orderBy('nama')->get();
        return view('master_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = MasterItem::with('kategori')->where('kode', $kode)->firstOrFail();
        return view('master_items.single.index', $data);
    }

    public function image($id)
    {
        $item = MasterItem::findOrFail($id);
        $path = $item->image['path'] ?? null;

        if (!$path || !str_starts_with($path, 'master-items/images/')) {
            abort(404);
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            abort(404);
        }

        return response()->file($disk->path($path));
    }

    public function print($kode)
    {
        $item = MasterItem::with('kategori')->where('kode', $kode)->firstOrFail();
        $printedAt = now();

        return Pdf::loadView('master_items.single.print', compact('item', 'printedAt'))
            ->setPaper('a4', 'portrait')
            ->stream('master-item-' . $item->kode . '.pdf');
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'kategori_id' => ['required', 'exists:kategori_items,id'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($method == 'new') {
            $data_item = new MasterItem;
            $kode = MasterItem::count('id');
            $kode = $kode + 1;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
            sleep(3);
        } else {
            $data_item = MasterItem::find($id);
            $kode = $data_item->kode;
        }

        $data_item->nama = $request->nama;
        $data_item->harga_beli = $request->harga_beli;
        $data_item->laba = $request->laba;
        $data_item->kode = $kode;
        $data_item->supplier = $request->supplier;
        $data_item->jenis = $request->jenis;
        $data_item->kategori_id = $request->kategori_id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('master-items/images', 'local');

            $data_item->image = [
                'disk' => 'local',
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
            ];
        }

        $data_item->save();

        return redirect('master-items');
    }

    public function delete($id)
    {
        MasterItem::find($id)->delete();
        return redirect('master-items');
    }

    public function updateRandomData()
    {
        $data = MasterItem::get();
        foreach($data as $item)
        {
            $kode = $item->id;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);

            $item->harga_beli = rand(100,1000000);
            $item->laba = rand(10,99);
            $item->kode = $kode;
            $item->supplier = $this->getRandomSupplier();
            $item->jenis = $this->getRandomJenis();
            $item->save();
        }
    }

    private function getRandomSupplier()
    {
        $array = ['Tokopaedi','Bukulapuk','TokoBagas','E Commurz','Blublu'];
        $random = rand(0,4);
        return $array[$random];
    }

    private function getRandomJenis()
    {
        $array = ['Obat','Alkes','Matkes','Umum','ATK'];
        $random = rand(0,4);
        return $array[$random];
    }
}
