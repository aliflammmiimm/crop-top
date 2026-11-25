<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\RecommendationHistory;
use App\Services\SpkCalculatorService;
use Database\Seeders\CropSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CropRecommendationBlackBoxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CropSeeder::class);
    }

    /**
     * Fake Open-Meteo response producing temperature=28.07857142857143, humidity=77.0, rainfall=158.4
     * (matching Kasus_Sistem in tests/Fixtures/test_cases_reference.json).
     */
    private function fakeOpenMeteoKasusSistem(): void
    {
        $temps = array_merge(
            array_fill(0, 7, 25.0),
            [28.2, 28.5, 28.0, 27.4, 27.8, 27.3, 28.3, 29.4, 27.8, 27.6, 28.6, 28.5, 28.3, 27.4],
            array_fill(0, 9, 28.0)
        );
        $humidities = array_merge(
            array_fill(0, 7, 70.0),
            [79.9, 82.8, 74.6, 78.2, 72.4, 74.8, 72.6, 79.1, 74.9, 81.1, 80.6, 79.3, 69.9, 77.8],
            array_fill(0, 9, 75.0)
        );
        $rainfalls = [
            15.2, 4.5, 5.7, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0,
            6.3, 0.0, 0.0, 7.9, 2.7, 5.8, 15.1, 15.2, 0.0, 14.3,
            11.9, 0.6, 0.0, 40.9, 0.0, 0.0, 0.0, 0.0, 8.3, 0.0,
        ];

        $this->fakeOpenMeteoArrays($temps, $humidities, $rainfalls);
    }

    /**
     * @param  array<int, float|null>  $temps
     * @param  array<int, float|null>  $humidities
     * @param  array<int, float|null>  $rainfalls
     */
    private function fakeOpenMeteoArrays(array $temps, array $humidities, array $rainfalls): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'daily' => [
                    'temperature_2m_mean' => $temps,
                    'relative_humidity_2m_mean' => $humidities,
                    'precipitation_sum' => $rainfalls,
                ],
            ], 200),
        ]);
    }

    public function test_skenario_01_valid_normal_input_produces_recommendation_and_persists_history(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Masukkan Data Tanah & Lokasi')
            ->assertSee('Cek Rekomendasi')
            ->assertSee('btn-reset-form', false)
            ->assertSee('const weatherEndpoint = "\/weather"', false)
            ->assertSee('Suhu 14h')
            ->assertSee('Kelembapan 14h')
            ->assertSee('Curah Hujan 30h')
            ->assertDontSee('Contoh: Surabaya');

        $this->fakeOpenMeteoKasusSistem();

        $response = $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertOk()
            ->assertSee('Rami (Jute)')
            ->assertSee('Sepakat')
            ->assertSee('28.08 °C')
            ->assertSee('77.00 %')
            ->assertSee('158.40 mm');

        $this->assertDatabaseCount('recommendation_histories', 1);
        $this->assertDatabaseHas('recommendation_histories', [
            'top1_wp_label' => 'jute',
            'top1_topsis_label' => 'jute',
            'agreement_status' => 'Sepakat',
        ]);
    }

    public function test_skenario_02_empty_inputs_are_rejected_without_calling_open_meteo(): void
    {
        Http::fake();

        $response = $this->from('/')->post('/recommend', []);

        $response->assertRedirect('/')
            ->assertSessionHasErrors(['N', 'P', 'K', 'ph', 'latitude', 'longitude']);

        Http::assertNothingSent();
        $this->assertDatabaseCount('recommendation_histories', 0);
    }

    public function test_skenario_03_non_numeric_inputs_are_rejected(): void
    {
        Http::fake();

        $response = $this->from('/')->post('/recommend', [
            'N' => 'abc',
            'P' => 'xyz',
            'K' => 'kalium',
            'ph' => 'netral',
            'latitude' => 'selatan',
            'longitude' => 'timur',
        ]);

        $response->assertRedirect('/')
            ->assertSessionHasErrors(['N', 'P', 'K', 'ph', 'latitude', 'longitude']);

        Http::assertNothingSent();
        $this->assertDatabaseCount('recommendation_histories', 0);
    }

    public function test_skenario_04_negative_nitrogen_is_rejected(): void
    {
        Http::fake();

        $response = $this->from('/')->post('/recommend', [
            'N' => -1,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertRedirect('/')
            ->assertSessionHasErrors(['N']);

        Http::assertNothingSent();
    }

    public function test_skenario_05_negative_phosphorus_and_potassium_are_rejected(): void
    {
        Http::fake();

        $response = $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => -5,
            'K' => -10,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertRedirect('/')
            ->assertSessionHasErrors(['P', 'K']);

        Http::assertNothingSent();
    }

    public function test_skenario_06_out_of_dataset_range_k_300_shows_amber_warning_and_continues_calculation(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $response = $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 300,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertOk()
            ->assertSee('alert-amber', false)
            ->assertSee('Peringatan Nilai di Luar Rentang Dataset')
            ->assertSee('Kalium (K)')
            ->assertDontSee('NAN');

        $history = RecommendationHistory::first();
        $this->assertNotNull($history);
        $this->assertNotEmpty($history->range_warnings);
        $this->assertGreaterThanOrEqual(0.0, $history->top1_topsis_score);
        $this->assertLessThanOrEqual(1.0, $history->top1_topsis_score);
    }

    public function test_skenario_07_ph_below_0_or_above_14_is_rejected(): void
    {
        Http::fake();

        $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => -0.1,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['ph']);

        $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 14.1,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['ph']);

        Http::assertNothingSent();
    }

    public function test_skenario_08_ph_boundary_0_and_14_are_accepted(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 0,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ])->assertOk()->assertSee('Peringatan Nilai di Luar Rentang Dataset');

        $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 14,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ])->assertOk()->assertSee('Peringatan Nilai di Luar Rentang Dataset');

        $this->assertDatabaseCount('recommendation_histories', 2);
    }

    public function test_skenario_09_latitude_outside_minus90_to_90_is_rejected_and_bounds_accepted(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -90.1,
            'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['latitude']);

        $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => 90.1,
            'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['latitude']);

        Http::assertNothingSent();

        $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -90,
            'longitude' => 112.7688,
        ])->assertOk();

        $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => 90,
            'longitude' => 112.7688,
        ])->assertOk();

        Http::assertSentCount(2);
    }

    public function test_skenario_10_longitude_outside_minus180_to_180_is_rejected_and_bounds_accepted(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => -180.1,
        ])->assertRedirect('/')->assertSessionHasErrors(['longitude']);

        $this->from('/')->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 180.1,
        ])->assertRedirect('/')->assertSessionHasErrors(['longitude']);

        Http::assertNothingSent();

        $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => -180,
        ])->assertOk();

        $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 180,
        ])->assertOk();

        Http::assertSentCount(2);
    }

    public function test_skenario_11_open_meteo_failure_timeout_or_incomplete_data_aborts_calculation(): void
    {
        // Case A: HTTP 500
        Http::fake(['api.open-meteo.com/*' => Http::response([], 500)]);
        $this->from('/')->post('/recommend', [
            'N' => 90, 'P' => 42, 'K' => 43, 'ph' => 6.5, 'latitude' => -7.2504, 'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['weather']);

        // Case B: ConnectionException / timeout
        Http::fake(function () {
            throw new ConnectionException('Timeout');
        });
        $this->from('/')->post('/recommend', [
            'N' => 90, 'P' => 42, 'K' => 43, 'ph' => 6.5, 'latitude' => -7.2504, 'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['weather']);

        // Case C: Incomplete data (null inside 30-day rainfall)
        $rainfalls = array_fill(0, 30, 5.0);
        $rainfalls[10] = null;
        $this->fakeOpenMeteoArrays(
            array_fill(0, 30, 28.0),
            array_fill(0, 30, 77.0),
            $rainfalls
        );
        $this->from('/')->post('/recommend', [
            'N' => 90, 'P' => 42, 'K' => 43, 'ph' => 6.5, 'latitude' => -7.2504, 'longitude' => 112.7688,
        ])->assertRedirect('/')->assertSessionHasErrors(['weather']);

        $this->assertDatabaseCount('recommendation_histories', 0);
    }

    public function test_skenario_12_random_forest_weights_from_model_params_sum_to_one(): void
    {
        $calculator = app(SpkCalculatorService::class);
        $weights = $calculator->getWeights();

        $this->assertCount(7, $weights);
        $this->assertEqualsWithDelta(1.0, array_sum($weights), 1e-12);
        $this->assertSame(22, Crop::count());
    }

    public function test_skenario_13_topsis_scores_within_zero_one_and_sorted_descending(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $response = $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertOk();
        $result = $response->viewData('result');

        $this->assertCount(22, $result['all_crops_topsis']);
        $prev = INF;
        foreach ($result['all_crops_topsis'] as $crop) {
            $this->assertGreaterThanOrEqual(0.0, $crop['V_TOPSIS']);
            $this->assertLessThanOrEqual(1.0, $crop['V_TOPSIS']);
            $this->assertLessThanOrEqual($prev, $crop['V_TOPSIS']);
            $prev = $crop['V_TOPSIS'];
        }
    }

    public function test_skenario_14_fuzzy_wp_scores_sorted_descending_for_all_22_crops(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $response = $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertOk();
        $result = $response->viewData('result');

        $this->assertCount(22, $result['all_crops']);
        $this->assertCount(5, $result['top5_wp']);

        $prev = INF;
        foreach ($result['all_crops'] as $crop) {
            $this->assertLessThanOrEqual($prev, $crop['P_WP']);
            $prev = $crop['P_WP'];
        }
    }

    public function test_skenario_15_primary_recommendation_follows_top1_fuzzy_wp_and_shows_berbeda_when_topsis_differs(): void
    {
        // Kasus_3 weather: temp=19.72702528, humidity=18.28173015, rainfall=143.7630894
        // Here Fuzzy WP #1 = kidneybeans (Kacang merah), TOPSIS #1 = chickpea (Kacang arab) -> Berbeda
        $this->fakeOpenMeteoArrays(
            array_fill(0, 30, 19.72702528),
            array_fill(0, 30, 18.28173015),
            array_fill(0, 30, 143.7630894 / 30.0)
        );

        $response = $this->post('/recommend', [
            'N' => 28,
            'P' => 58,
            'K' => 24,
            'ph' => 5.748190463,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertOk()
            ->assertSee('Kacang merah (Kidneybeans)')
            ->assertSee('Berbeda')
            ->assertSee('TOPSIS #1: Kacang arab');

        $this->assertDatabaseHas('recommendation_histories', [
            'top1_wp_label' => 'kidneybeans',
            'top1_topsis_label' => 'chickpea',
            'agreement_status' => 'Berbeda',
        ]);
    }

    public function test_skenario_16_ui_elements_modals_print_history_and_photo_fallback(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $response = $this->post('/recommend', [
            'N' => 90,
            'P' => 42,
            'K' => 43,
            'ph' => 6.5,
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);

        $response->assertOk()
            ->assertSee('Suhu 14h')
            ->assertSee('Kelembapan 14h')
            ->assertSee('Curah Hujan 30h')
            ->assertSee('/images/crops/jute.jpg', false)
            ->assertSee('/images/crops/fallback.svg', false)
            ->assertSee('Detail')
            ->assertSee('Lihat semua 22 tanaman')
            ->assertSee('Cetak Hasil')
            ->assertSee('@media print', false)
            ->assertSee('modal-detail', false)
            ->assertSee('modal-all-crops', false);

        // Reload saved history from MySQL/DB via ?history={id}
        $history = RecommendationHistory::firstOrFail();
        $this->get('/?history='.$history->id)
            ->assertOk()
            ->assertSee('Rami (Jute)')
            ->assertSee('Sepakat');

        // Verify fallback SVG when a crop photo does not exist
        $tempPhoto = public_path('images/crops/jute.jpg');
        $backupPhoto = public_path('images/crops/jute.jpg.bak');
        rename($tempPhoto, $backupPhoto);

        try {
            $result = app(SpkCalculatorService::class)->calculate([
                'N' => 90,
                'P' => 42,
                'K' => 43,
                'temperature' => 28.07857142857143,
                'humidity' => 77.0,
                'ph' => 6.5,
                'rainfall' => 158.4,
            ]);
            $this->assertFalse($result['primary_recommendation']['has_photo']);
            $this->assertSame('/images/crops/fallback.svg', $result['primary_recommendation']['image_url']);
        } finally {
            rename($backupPhoto, $tempPhoto);
        }
    }

    public function test_weather_preview_endpoint_returns_aggregated_weather_and_handles_errors(): void
    {
        $this->fakeOpenMeteoKasusSistem();

        $this->getJson('/weather?latitude=-7.2504&longitude=112.7688')
            ->assertOk()
            ->assertJson([
                'temperature_formatted' => '28.08 °C',
                'humidity_formatted' => '77.00 %',
                'rainfall_formatted' => '158.40 mm',
            ]);

        $this->getJson('/weather?latitude=95&longitude=112.7688')
            ->assertUnprocessable();
    }

    public function test_weather_preview_endpoint_returns_502_when_open_meteo_fails(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 500)]);

        $this->getJson('/weather?latitude=-7.2504&longitude=112.7688')
            ->assertStatus(502);
    }
}
