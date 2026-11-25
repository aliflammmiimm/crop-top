<?php

namespace Tests\Unit;

use App\Services\SpkCalculatorService;
use Tests\TestCase;

class SpkCalculatorTest extends TestCase
{
    private SpkCalculatorService $calculator;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $referenceCases;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new SpkCalculatorService;
        $fixturePath = base_path('tests/Fixtures/test_cases_reference.json');
        $this->referenceCases = json_decode((string) file_get_contents($fixturePath), true);
    }

    public function test_random_forest_weights_sum_to_one(): void
    {
        $weights = $this->calculator->getWeights();

        $this->assertCount(7, $weights);
        $this->assertEqualsWithDelta(1.0, array_sum($weights), 1e-12);
    }

    public function test_kasus_sistem_matches_reference_scores_and_ranks_within_tolerance(): void
    {
        $kasusSistem = collect($this->referenceCases)->firstWhere('kasus', 'Kasus_Sistem');
        $this->assertNotNull($kasusSistem);

        $result = $this->calculator->calculate($kasusSistem['input']);

        $this->assertSame($kasusSistem['rekomendasi_utama_wp'], $result['primary_recommendation']['label']);
        $this->assertSame($kasusSistem['top1_topsis'], $result['topsis_top1']['label']);
        $this->assertSame('Sepakat', $result['agreement_status']);

        $byLabel = collect($result['all_crops'])->keyBy('label');

        foreach ($kasusSistem['top5_topsis'] as $expected) {
            $actual = $byLabel->get($expected['Tanaman']);
            $this->assertNotNull($actual);
            $this->assertSame($expected['Kode'], $actual['kode']);
            $this->assertSame($expected['Rank_TOPSIS'], $actual['Rank_TOPSIS']);
            $this->assertEqualsWithDelta($expected['V_TOPSIS'], $actual['V_TOPSIS'], 1e-6);
        }

        foreach ($kasusSistem['top5_wp'] as $expected) {
            $actual = $byLabel->get($expected['Tanaman']);
            $this->assertNotNull($actual);
            $this->assertSame($expected['Kode'], $actual['kode']);
            $this->assertSame($expected['Rank_WP'], $actual['Rank_WP']);
            $this->assertEqualsWithDelta($expected['lnP'], $actual['lnP'], 1e-6);
            $this->assertEqualsWithDelta($expected['P_WP'], $actual['P_WP'], 1e-6);
        }
    }

    public function test_kasus_sistem_all_22_crops_match_csv_reference_within_tolerance(): void
    {
        $kasusSistem = collect($this->referenceCases)->firstWhere('kasus', 'Kasus_Sistem');
        $result = $this->calculator->calculate($kasusSistem['input']);
        $byLabel = collect($result['all_crops'])->keyBy('label');

        $csvRows = array_map('str_getcsv', file(base_path('research/hasil_model_perbandingan.csv')));
        $header = array_shift($csvRows);

        $checked = 0;
        foreach ($csvRows as $row) {
            $assoc = array_combine($header, $row);
            if ($assoc['Kasus'] !== 'Kasus_Sistem') {
                continue;
            }

            $actual = $byLabel->get($assoc['Tanaman']);
            $this->assertNotNull($actual);
            $this->assertSame($assoc['Kode'], $actual['kode']);
            $this->assertSame((int) $assoc['Rank_TOPSIS'], $actual['Rank_TOPSIS']);
            $this->assertSame((int) $assoc['Rank_WP'], $actual['Rank_WP']);
            $this->assertEqualsWithDelta((float) $assoc['V_TOPSIS'], $actual['V_TOPSIS'], 1e-6);
            $this->assertEqualsWithDelta((float) $assoc['lnP'], $actual['lnP'], 1e-6);
            $this->assertEqualsWithDelta((float) $assoc['P_WP'], $actual['P_WP'], 1e-6);
            $checked++;
        }

        $this->assertSame(22, $checked);
    }

    public function test_all_reference_cases_match_top1_recommendation_and_topsis_agreement_status(): void
    {
        foreach ($this->referenceCases as $case) {
            $result = $this->calculator->calculate($case['input']);

            $this->assertSame(
                $case['rekomendasi_utama_wp'],
                $result['primary_recommendation']['label'],
                "Primary Fuzzy WP mismatch on {$case['kasus']}"
            );
            $this->assertSame(
                $case['top1_topsis'],
                $result['topsis_top1']['label'],
                "Top-1 TOPSIS mismatch on {$case['kasus']}"
            );

            $expectedAgreement = $case['rekomendasi_utama_wp'] === $case['top1_topsis'] ? 'Sepakat' : 'Berbeda';
            $this->assertSame($expectedAgreement, $result['agreement_status']);
        }
    }

    public function test_extreme_out_of_range_k_300_produces_valid_non_nan_scores_and_warning(): void
    {
        $result = $this->calculator->calculate([
            'N' => 90,
            'P' => 42,
            'K' => 300,
            'temperature' => 28.07857142857143,
            'humidity' => 77.0,
            'ph' => 6.5,
            'rainfall' => 158.4,
        ]);

        $this->assertNotEmpty($result['range_warnings']);
        $this->assertSame('K', $result['range_warnings'][0]['feature']);
        $this->assertCount(22, $result['all_crops']);

        foreach ($result['all_crops'] as $crop) {
            $this->assertFalse(is_nan($crop['V_TOPSIS']));
            $this->assertFalse(is_nan($crop['lnP']));
            $this->assertFalse(is_nan($crop['P_WP']));
            $this->assertGreaterThanOrEqual(0.0, $crop['V_TOPSIS']);
            $this->assertLessThanOrEqual(1.0, $crop['V_TOPSIS']);
        }
    }

    public function test_topsis_and_fuzzy_wp_lists_are_sorted_descending_by_score(): void
    {
        $kasus3 = collect($this->referenceCases)->firstWhere('kasus', 'Kasus_3');
        $result = $this->calculator->calculate($kasus3['input']);

        $this->assertCount(22, $result['all_crops']);
        $this->assertCount(22, $result['all_crops_topsis']);
        $this->assertSame('Berbeda', $result['agreement_status']);

        $prevLnP = INF;
        foreach ($result['all_crops'] as $crop) {
            $this->assertLessThanOrEqual($prevLnP, $crop['lnP']);
            $prevLnP = $crop['lnP'];
        }

        $prevV = INF;
        foreach ($result['all_crops_topsis'] as $crop) {
            $this->assertLessThanOrEqual($prevV, $crop['V_TOPSIS']);
            $prevV = $crop['V_TOPSIS'];
        }
    }
}
