@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <a href="{{ route('kategori-items.create') }}" class="btn btn-secondary">+ Kategori Baru</a>
                <a href="{{ url('master-items') }}" class="btn btn-outline-secondary">Kembali ke Master Items</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">Daftar Kategori Items</div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Jumlah Item</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoriItems as $kategoriItem)
                                    <tr>
                                        <td>{{ $kategoriItem->id }}</td>
                                        <td>{{ $kategoriItem->nama }}</td>
                                        <td>{{ $kategoriItem->items_count }}</td>
                                        <td class="text-end text-nowrap">
                                            <a
                                                href="{{ route('kategori-items.edit', $kategoriItem) }}"
                                                class="btn btn-sm btn-info"
                                            >
                                                Edit
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('kategori-items.destroy', $kategoriItem) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Belum ada kategori.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
