<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationHistory extends Model
{
    protected $fillable = [
        'n',
        'p',
        'k',
        'ph',
        'latitude',
        'longitude',
        'temperature',
        'humidity',
        'rainfall',
        'top1_wp_kode',
        'top1_wp_label',
        'top1_wp_nama_id',
        'top1_wp_score',
        'top1_wp_ln_p',
        'top1_topsis_kode',
        'top1_topsis_label',
        'top1_topsis_nama_id',
        'top1_topsis_score',
        'agreement_status',
        'range_warnings',
        'results_json',
    ];

    protected function casts(): array
    {
        return [
            'n' => 'float',
            'p' => 'float',
            'k' => 'float',
            'ph' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
            'temperature' => 'float',
            'humidity' => 'float',
            'rainfall' => 'float',
            'top1_wp_score' => 'float',
            'top1_wp_ln_p' => 'float',
            'top1_topsis_score' => 'float',
            'range_warnings' => 'array',
            'results_json' => 'array',
        ];
    }
}
