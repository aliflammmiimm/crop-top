<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $fillable = [
        'kode',
        'label',
        'nama_id',
        'mu',
        'sd',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'mu' => 'array',
            'sd' => 'array',
        ];
    }
}
