<?php

namespace App\Http\Controllers;

use App\Exceptions\WeatherFetchException;
use App\Http\Requests\RecommendationRequest;
use App\Models\RecommendationHistory;
use App\Services\OpenMeteoService;
use App\Services\SpkCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CropRecommendationController extends Controller
{
    public function __construct(
        private readonly SpkCalculatorService $calculator,
        private readonly OpenMeteoService $openMeteo,
    ) {}

    public function index(Request $request): View
    {
        $result = null;
        $formInput = [];

        $hasHistoryTable = Schema::hasTable('recommendation_histories');

        if ($hasHistoryTable && $request->filled('history')) {
            $history = RecommendationHistory::find($request->integer('history'));
            if ($history !== null) {
                $result = $history->results_json;
                $result['location'] = [
                    'latitude' => $history->latitude,
                    'longitude' => $history->longitude,
                ];
                $result['history_id'] = $history->id;
                $result['created_at'] = $history->created_at?->format('d M Y H:i');

                $formInput = [
                    'N' => $history->n,
                    'P' => $history->p,
                    'K' => $history->k,
                    'ph' => $history->ph,
                    'latitude' => $history->latitude,
                    'longitude' => $history->longitude,
                ];
            }
        }

        return view('recommendation.index', [
            'result' => $result,
            'formInput' => $formInput,
            'weights' => $this->calculator->getWeights(),
            'datasetRanges' => $this->calculator->getDatasetRanges(),
            'recentHistories' => $hasHistoryTable ? RecommendationHistory::latest()->limit(5)->get() : collect(),
        ]);
    }

    public function weather(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
        ]);

        try {
            $weather = $this->openMeteo->fetchAggregatedWeather(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
        } catch (WeatherFetchException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }

        $current = $weather['current'] ?? null;
        $currentSummary = $current !== null
            ? sprintf(
                'Cuaca sesaat sekarang: %.1f °C · Kelembapan %.0f%% · Hujan %.1f mm (SPK memakai agregasi 14 & 30 hari di atas).',
                $current['temperature'],
                $current['humidity'],
                $current['rainfall']
            )
            : 'Suhu & kelembapan adalah rata-rata 14 hari (7h lalu + 7h prakiraan); curah hujan adalah total 30 hari.';

        return response()->json([
            'temperature' => $weather['temperature'],
            'humidity' => $weather['humidity'],
            'rainfall' => $weather['rainfall'],
            'current' => $current,
            'current_summary' => $currentSummary,
            'temperature_formatted' => number_format($weather['temperature'], 2).' °C',
            'humidity_formatted' => number_format($weather['humidity'], 2).' %',
            'rainfall_formatted' => number_format($weather['rainfall'], 2).' mm',
        ]);
    }

    public function recommend(RecommendationRequest $request): View|RedirectResponse
    {
        $validated = $request->validated();
        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];

        try {
            $weather = $this->openMeteo->fetchAggregatedWeather($latitude, $longitude);
        } catch (WeatherFetchException $e) {
            return redirect()
                ->route('home')
                ->withInput()
                ->withErrors(['weather' => $e->getMessage()])
                ->with('weather_error', $e->getMessage());
        }

        $fullInput = [
            'N' => (float) $validated['N'],
            'P' => (float) $validated['P'],
            'K' => (float) $validated['K'],
            'temperature' => $weather['temperature'],
            'humidity' => $weather['humidity'],
            'ph' => (float) $validated['ph'],
            'rainfall' => $weather['rainfall'],
        ];

        $result = $this->calculator->calculate($fullInput);
        $result['weather']['current'] = $weather['current'] ?? null;
        $result['location'] = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        $primary = $result['primary_recommendation'];
        $topsisTop1 = $result['topsis_top1'];

        $history = RecommendationHistory::create([
            'n' => $fullInput['N'],
            'p' => $fullInput['P'],
            'k' => $fullInput['K'],
            'ph' => $fullInput['ph'],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'temperature' => $weather['temperature'],
            'humidity' => $weather['humidity'],
            'rainfall' => $weather['rainfall'],
            'top1_wp_kode' => $primary['kode'],
            'top1_wp_label' => $primary['label'],
            'top1_wp_nama_id' => $primary['nama_id'],
            'top1_wp_score' => $primary['P_WP'],
            'top1_wp_ln_p' => $primary['lnP'],
            'top1_topsis_kode' => $topsisTop1['kode'],
            'top1_topsis_label' => $topsisTop1['label'],
            'top1_topsis_nama_id' => $topsisTop1['nama_id'],
            'top1_topsis_score' => $topsisTop1['V_TOPSIS'],
            'agreement_status' => $result['agreement_status'],
            'range_warnings' => $result['range_warnings'],
            'results_json' => $result,
        ]);

        $result['history_id'] = $history->id;
        $result['created_at'] = $history->created_at?->format('d M Y H:i');

        return view('recommendation.index', [
            'result' => $result,
            'formInput' => [
                'N' => $validated['N'],
                'P' => $validated['P'],
                'K' => $validated['K'],
                'ph' => $validated['ph'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            'weights' => $this->calculator->getWeights(),
            'datasetRanges' => $this->calculator->getDatasetRanges(),
            'recentHistories' => RecommendationHistory::latest()->limit(5)->get(),
        ]);
    }
}
