<?php

namespace App\Http\Controllers;

use App\Models\KategoriItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriItemsController extends Controller
{
    public function index()
    {
        return view('kategori_items.index', [
            'kategoriItems' => KategoriItem::withCount([
                'items' => fn ($query) => $query,
            ])->orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        return view('kategori_items.form', [
            'kategoriItem' => new KategoriItem(),
            'method' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:kategori_items,nama'],
        ]);

        KategoriItem::create($validated);

        return redirect()
            ->route('kategori-items.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(KategoriItem $kategoriItem)
    {
        return view('kategori_items.form', [
            'kategoriItem' => $kategoriItem,
            'method' => 'edit',
        ]);
    }

    public function update(Request $request, KategoriItem $kategoriItem)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_items', 'nama')->ignore($kategoriItem->id),
            ],
        ]);

        $kategoriItem->update($validated);

        return redirect()
            ->route('kategori-items.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriItem $kategoriItem)
    {
        if ($kategoriItem->items()->withTrashed()->exists()) {
            return redirect()
                ->route('kategori-items.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh Master Item.');
        }

        $kategoriItem->delete();

        return redirect()
            ->route('kategori-items.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
