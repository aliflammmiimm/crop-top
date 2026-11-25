<?php

namespace Database\Seeders;

use App\Models\Crop;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/spk/model_params.json');
        $params = json_decode((string) file_get_contents($path), true);

        foreach ($params['crops'] as $crop) {
            Crop::updateOrCreate(
                ['kode' => $crop['kode']],
                [
                    'label' => $crop['label'],
                    'nama_id' => $crop['nama_id'],
                    'mu' => $crop['mu'],
                    'sd' => $crop['sd'],
                    'image_path' => "images/crops/{$crop['label']}.jpg",
                ]
            );
        }
    }
}
