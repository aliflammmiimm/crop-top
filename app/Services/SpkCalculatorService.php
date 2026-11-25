<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class SpkCalculatorService
{
    public const FEATURE_META = [
        'N' => ['label' => 'Nitrogen (N)', 'unit' => 'mg/kg'],
        'P' => ['label' => 'Fosfor (P)', 'unit' => 'mg/kg'],
        'K' => ['label' => 'Kalium (K)', 'unit' => 'mg/kg'],
        'temperature' => ['label' => 'Suhu (14h)', 'unit' => '°C'],
        'humidity' => ['label' => 'Kelembapan (14h)', 'unit' => '%'],
        'ph' => ['label' => 'pH Tanah', 'unit' => ''],
        'rainfall' => ['label' => 'Curah Hujan (30h)', 'unit' => 'mm'],
    ];

    /**
     * @var array<string, mixed>
     */
    private array $params;

    public function __construct(?string $modelParamsPath = null)
    {
        $path = $modelParamsPath ?? storage_path('app/spk/model_params.json');

        if (! file_exists($path)) {
            throw new RuntimeException("Model parameter file not found at: {$path}");
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded) || ! isset($decoded['features'], $decoded['weights'], $decoded['crops'], $decoded['metadata'])) {
            throw new RuntimeException('Invalid model_params.json structure.');
        }

        $this->params = $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    public function getModelParams(): array
    {
        return $this->params;
    }

    /**
     * @return array<string, float>
     */
    public function getWeights(): array
    {
        return array_map(static fn ($v): float => (float) $v, $this->params['weights']);
    }

    /**
     * @return array<string, array{min: float, max: float}>
     */
    public function getDatasetRanges(): array
    {
        return $this->params['dataset_ranges'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCrops(): array
    {
        return $this->params['crops'];
    }

    /**
     * Check whether any valid input value falls outside dataset_ranges.
     *
     * @param  array<string, float|int|string>  $input
     * @return array<int, array{feature: string, label: string, value: float, min: float, max: float, message: string}>
     */
    public function checkRangeWarnings(array $input): array
    {
        $warnings = [];
        $ranges = $this->getDatasetRanges();

        foreach ($this->params['features'] as $feature) {
            if (! isset($input[$feature], $ranges[$feature])) {
                continue;
            }

            $value = (float) $input[$feature];
            $min = (float) $ranges[$feature]['min'];
            $max = (float) $ranges[$feature]['max'];

            if ($value < $min || $value > $max) {
                $label = self::FEATURE_META[$feature]['label'] ?? $feature;
                $unit = self::FEATURE_META[$feature]['unit'] ?? '';
                $unitSuffix = $unit !== '' ? " {$unit}" : '';
                $minFormatted = number_format($min, 2, ',', '.');
                $maxFormatted = number_format($max, 2, ',', '.');
                $valFormatted = number_format($value, 2, ',', '.');

                $warnings[] = [
                    'feature' => $feature,
                    'label' => $label,
                    'value' => $value,
                    'min' => $min,
                    'max' => $max,
                    'message' => "{$label} ({$valFormatted}{$unitSuffix}) berada di luar rentang dataset ({$minFormatted} – {$maxFormatted}{$unitSuffix}). Perhitungan tetap dilanjutkan dengan pengaman numerik.",
                ];
            }
        }

        return $warnings;
    }

    /**
     * Compute Gaussian suitability, TOPSIS, and Fuzzy WP scores and rankings for all 22 crops.
     *
     * @param  array<string, float|int|string>  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $features = $this->params['features'];
        $normalizedInput = [];

        foreach ($features as $feature) {
            if (! array_key_exists($feature, $input) || ! is_numeric($input[$feature])) {
                throw new InvalidArgumentException("Missing or non-numeric input feature: {$feature}");
            }
            $normalizedInput[$feature] = (float) $input[$feature];
        }

        $weights = $this->getWeights();
        $crops = $this->params['crops'];
        $eps = (float) ($this->params['metadata']['eps'] ?? 1e-9);
        $sigmaAdd = (float) ($this->params['metadata']['sigma_add'] ?? 1e-6);

        // 1. Gaussian suitability: S_ij = exp(-0.5 * ((x_j - mu_ij) / sd_ij)^2)
        // Note: sd in model_params.json already includes + sigma_add.
        $suitability = [];
        foreach ($crops as $idx => $crop) {
            foreach ($features as $feature) {
                $mu = (float) $crop['mu'][$feature];
                $sd = (float) $crop['sd'][$feature];
                if ($sd <= 0.0) {
                    $sd = $sigmaAdd;
                }
                $z = ($normalizedInput[$feature] - $mu) / $sd;
                $suitability[$idx][$feature] = exp(-0.5 * $z * $z);
            }
        }

        // 2. TOPSIS vector normalization with S_safe = S_ij + eps
        $normDenom = [];
        foreach ($features as $feature) {
            $sumSquares = 0.0;
            foreach ($crops as $idx => $crop) {
                $sSafe = $suitability[$idx][$feature] + $eps;
                $sumSquares += $sSafe * $sSafe;
            }
            $normDenom[$feature] = sqrt($sumSquares);
        }

        // 3. Weighted normalized matrix y_ij and ideal positive (A+) / negative (A-)
        $weightedNormalized = [];
        $idealPos = [];
        $idealNeg = [];
        foreach ($features as $feature) {
            $idealPos[$feature] = -INF;
            $idealNeg[$feature] = INF;
            $denom = $normDenom[$feature];

            foreach ($crops as $idx => $crop) {
                $sSafe = $suitability[$idx][$feature] + $eps;
                $normVal = $denom > 0.0 ? ($sSafe / $denom) : 0.0;
                $yVal = $weights[$feature] * $normVal;
                $weightedNormalized[$idx][$feature] = $yVal;

                if ($yVal > $idealPos[$feature]) {
                    $idealPos[$feature] = $yVal;
                }
                if ($yVal < $idealNeg[$feature]) {
                    $idealNeg[$feature] = $yVal;
                }
            }
        }

        // 4. Separation distances (D+, D-), TOPSIS score V_i, and Fuzzy WP (lnP, P_WP)
        $rawResults = [];
        foreach ($crops as $idx => $crop) {
            $dpSq = 0.0;
            $dmSq = 0.0;
            $lnP = 0.0;
            $criteriaDetails = [];

            foreach ($features as $feature) {
                $yVal = $weightedNormalized[$idx][$feature];
                $dpDiff = $yVal - $idealPos[$feature];
                $dmDiff = $yVal - $idealNeg[$feature];
                $dpSq += $dpDiff * $dpDiff;
                $dmSq += $dmDiff * $dmDiff;

                $sVal = $suitability[$idx][$feature];
                $lnP += $weights[$feature] * log($sVal + $eps);

                $meta = self::FEATURE_META[$feature] ?? ['label' => $feature, 'unit' => ''];
                $criteriaDetails[$feature] = [
                    'feature' => $feature,
                    'label' => $meta['label'],
                    'unit' => $meta['unit'],
                    'input' => $normalizedInput[$feature],
                    'mu' => (float) $crop['mu'][$feature],
                    'sd' => (float) $crop['sd'][$feature],
                    'weight' => $weights[$feature],
                    'suitability' => $sVal,
                ];
            }

            $dp = sqrt($dpSq);
            $dm = sqrt($dmSq);
            $denomDist = $dp + $dm;
            $vTopsis = $denomDist > 0.0 ? ($dm / $denomDist) : 0.0;
            $pWp = exp($lnP);

            $label = (string) $crop['label'];
            $relativeImagePath = "images/crops/{$label}.jpg";
            $hasPhoto = file_exists(public_path($relativeImagePath));

            $rawResults[$idx] = [
                'kode' => (string) $crop['kode'],
                'label' => $label,
                'nama_id' => (string) $crop['nama_id'],
                'image_path' => $relativeImagePath,
                'image_url' => $hasPhoto ? "/{$relativeImagePath}" : '/images/crops/fallback.svg',
                'has_photo' => $hasPhoto,
                'V_TOPSIS' => $vTopsis,
                'D_plus' => $dp,
                'D_minus' => $dm,
                'lnP' => $lnP,
                'P_WP' => $pWp,
                'criteria' => $criteriaDetails,
            ];
        }

        // 5. Assign competition ranks matching notebook: r = 1 + count(score_k > score_i)
        $nCrops = count($rawResults);
        for ($i = 0; $i < $nCrops; $i++) {
            $rankTopsis = 1;
            $rankWp = 1;
            for ($k = 0; $k < $nCrops; $k++) {
                if ($rawResults[$k]['V_TOPSIS'] > $rawResults[$i]['V_TOPSIS']) {
                    $rankTopsis++;
                }
                if ($rawResults[$k]['lnP'] > $rawResults[$i]['lnP']) {
                    $rankWp++;
                }
            }
            $rawResults[$i]['Rank_TOPSIS'] = $rankTopsis;
            $rawResults[$i]['Rank_WP'] = $rankWp;
        }

        // Sort by Fuzzy WP rank (primary recommendation system)
        $allByWp = $rawResults;
        usort($allByWp, static function (array $a, array $b): int {
            return [$a['Rank_WP'], -$a['lnP'], $a['kode']] <=> [$b['Rank_WP'], -$b['lnP'], $b['kode']];
        });

        // Sort by TOPSIS rank
        $allByTopsis = $rawResults;
        usort($allByTopsis, static function (array $a, array $b): int {
            return [$a['Rank_TOPSIS'], -$a['V_TOPSIS'], $a['kode']] <=> [$b['Rank_TOPSIS'], -$b['V_TOPSIS'], $b['kode']];
        });

        $primaryRecommendation = $allByWp[0];
        $topsisTop1 = $allByTopsis[0];
        $isAgreed = $primaryRecommendation['label'] === $topsisTop1['label'];

        return [
            'input' => $normalizedInput,
            'weather' => [
                'temperature' => $normalizedInput['temperature'],
                'humidity' => $normalizedInput['humidity'],
                'rainfall' => $normalizedInput['rainfall'],
            ],
            'weights' => $weights,
            'dataset_ranges' => $this->getDatasetRanges(),
            'range_warnings' => $this->checkRangeWarnings($normalizedInput),
            'primary_recommendation' => $primaryRecommendation,
            'topsis_top1' => $topsisTop1,
            'agreement_status' => $isAgreed ? 'Sepakat' : 'Berbeda',
            'is_agreed' => $isAgreed,
            'top5_wp' => array_slice($allByWp, 0, 5),
            'top5_topsis' => array_slice($allByTopsis, 0, 5),
            'all_crops' => $allByWp,
            'all_crops_topsis' => $allByTopsis,
        ];
    }
}
