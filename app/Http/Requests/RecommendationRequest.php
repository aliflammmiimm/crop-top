<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'N' => ['required', 'numeric', 'min:0'],
            'P' => ['required', 'numeric', 'min:0'],
            'K' => ['required', 'numeric', 'min:0'],
            'ph' => ['required', 'numeric', 'min:0', 'max:14'],
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'N' => 'Nitrogen (N)',
            'P' => 'Fosfor (P)',
            'K' => 'Kalium (K)',
            'ph' => 'pH Tanah',
            'latitude' => 'Lintang',
            'longitude' => 'Bujur',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka yang valid.',
            'N.min' => 'Nitrogen (N) tidak boleh bernilai negatif.',
            'P.min' => 'Fosfor (P) tidak boleh bernilai negatif.',
            'K.min' => 'Kalium (K) tidak boleh bernilai negatif.',
            'ph.min' => 'pH Tanah harus berada di antara 0 hingga 14.',
            'ph.max' => 'pH Tanah harus berada di antara 0 hingga 14.',
            'latitude.min' => 'Lintang harus berada di antara -90 hingga 90.',
            'latitude.max' => 'Lintang harus berada di antara -90 hingga 90.',
            'longitude.min' => 'Bujur harus berada di antara -180 hingga 180.',
            'longitude.max' => 'Bujur harus berada di antara -180 hingga 180.',
        ];
    }
}
