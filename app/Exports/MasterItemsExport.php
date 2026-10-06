<?php

namespace App\Exports;

use App\Models\MasterItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class MasterItemsExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    private int $rowNumber = 0;

    public function query(): Builder
    {
        return MasterItem::query()
            ->with('kategori:id,nama')
            ->orderBy('id');
    }

    public function headings(): array
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

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item->kategori?->nama ?? '-',
            $item->nama,
            $item->supplier,
            $item->harga_beli,
            $item->laba,
            $item->harga_beli + ($item->harga_beli * $item->laba / 100),
        ];
    }
}
