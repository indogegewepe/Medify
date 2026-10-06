<div id="filter-container">
    <h4>Filter</h4>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="filter-kategori">Kategori</label>
                <select class="form-control" id="filter-kategori">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriItems as $kategoriItem)
                        <option value="{{ $kategoriItem->id }}">{{ $kategoriItem->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" class="form-control" id="filter-kode">
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" class="form-control" id="filter-nama">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Harga Min</label>
                <input type="number" class="form-control" id="filter-harga-min">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Harga Max</label>
                <input type="number" class="form-control" id="filter-harga-max">
            </div>
        </div>
    </div>
    <button class="btn btn-primary mt-1 btn-get-data">Filter</button>
    <span id="loading-filter" style="display: none;">Loading...</span>
</div>
