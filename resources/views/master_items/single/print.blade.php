<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Item {{ $item->kode }}</title>
    <style>
        @page {
            margin: 36px 40px 58px;
        }

        body {
            color: #212529;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 22px;
            text-align: center;
        }

        .category {
            margin-bottom: 20px;
            width: 100%;
        }

        .category td {
            padding: 3px 0;
        }

        .category .label {
            font-weight: bold;
            width: 120px;
        }

        .category .separator {
            width: 14px;
        }

        .items {
            border-collapse: collapse;
            width: 100%;
        }

        .items th,
        .items td {
            border: 1px solid #6c757d;
            padding: 8px 6px;
            vertical-align: top;
        }

        .items th {
            background: #e9ecef;
            font-size: 10px;
            text-align: left;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .footer {
            bottom: -38px;
            color: #6c757d;
            font-size: 10px;
            left: 0;
            position: fixed;
            right: 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Detail Master Item</h1>

    <table class="category">
        <tr>
            <td class="label">Nama Kategori</td>
            <td class="separator">:</td>
            <td>{{ $item->kategori->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kode Kategori</td>
            <td class="separator">:</td>
            <td>{{ $item->kategori->id ?? '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Harga Beli</th>
                <th>Laba</th>
                <th>Harga Jual</th>
                <th>Supplier</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $item->nama }}</td>
                <td class="number">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                <td class="number">{{ number_format($item->laba, 0, ',', '.') }}%</td>
                <td class="number">{{ number_format($item->harga_beli + ($item->harga_beli * $item->laba / 100), 0, ',', '.') }}</td>
                <td>{{ $item->supplier }}</td>
                <td>{{ $item->jenis }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ $printedAt->format('d-m-Y H:i:s') }}
    </div>
</body>
</html>
