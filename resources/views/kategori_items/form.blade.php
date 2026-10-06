@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-2">
                <a href="{{ route('kategori-items.index') }}" class="btn btn-secondary">
                    Kembali ke Daftar Kategori
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    {{ $method === 'create' ? 'Buat Kategori Baru' : 'Edit Kategori' }}
                </div>

                <div class="card-body">
                    <form
                        method="POST"
                        action="{{ $method === 'create'
                            ? route('kategori-items.store')
                            : route('kategori-items.update', $kategoriItem) }}"
                    >
                        @csrf
                        @if($method === 'edit')
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                class="form-control @error('nama') is-invalid @enderror"
                                value="{{ old('nama', $kategoriItem->nama) }}"
                                maxlength="255"
                                required
                                autofocus
                            >
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            {{ $method === 'create' ? 'Simpan' : 'Perbarui' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
