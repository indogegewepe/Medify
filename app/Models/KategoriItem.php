<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'nama',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MasterItem::class, 'kategori_id');
    }
}
