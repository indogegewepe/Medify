<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'image' => 'array',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriItem::class, 'kategori_id');
    }
}
