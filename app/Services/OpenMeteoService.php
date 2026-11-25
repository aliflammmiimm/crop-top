<?php

namespace App\Services;

use App\Exceptions\WeatherFetchException;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenMeteoService
{
    public const ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

    public const PAST_DAYS = 14;

    public const FORECAST_DAYS = 16;

    public const WINDOW_N = 14; // 7 hari terakhir + 7 hari prakiraan

    public const WINDOW_M = 30; // 14 hari terakhir + 16 hari prakiraan

    /**
     * Fetch 30-day daily weather from Open-Meteo and aggregate 14-day mean temp/humidity & 30-day rainfall sum.
     *
     * @return array{temperature: float, humidity: float, rainfall: float, window_n: int, window_m: int}
     *
     * @throws WeatherFetchException
     */
    public function fetchAggregatedWeather(float $latitude, float $longitude): array
    {
        try {
            $response = Http::withOptions([
                'force_ip_resolve' => 'v4',
            ])
                ->timeout(15)
                ->connectTimeout(10)
                ->retry(2, 100, throw: false)
                ->acceptJson()
                ->get(self::ENDPOINT, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'daily' => 'temperature_2m_mean,relative_humidity_2m_mean,precipitation_sum',
                    'past_days' => self::PAST_DAYS,
                    'forecast_days' => self::FORECAST_DAYS,
                    'timezone' => 'auto',
                ]);
        } catch (Throwable $e) {
            throw new WeatherFetchException(
                'Gagal menghubungi layanan cuaca Open-Meteo (koneksi terputus atau timeout). Perhitungan tidak dapat dilanjutkan.',
                0,
                $e
            );
        }

        if ($response->failed()) {
            throw new WeatherFetchException(
                "Layanan cuaca Open-Meteo mengembalikan status galat (HTTP {$response->status()}). Perhitungan tidak dapat dilanjutkan."
            );
        }

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['daily']) || ! is_array($payload['daily'])) {
            throw new WeatherFetchException(
                'Respon data cuaca Open-Meteo tidak valid. Perhitungan tidak dapat dilanjutkan.'
            );
        }

        $daily = $payload['daily'];
        $temps = $daily['temperature_2m_mean'] ?? null;
        $humidities = $daily['relative_humidity_2m_mean'] ?? null;
        $rainfalls = $daily['precipitation_sum'] ?? null;

        if (! is_array($temps) || ! is_array($humidities) || ! is_array($rainfalls)) {
            throw new WeatherFetchException(
                'Data harian suhu, kelembapan, atau curah hujan dari Open-Meteo tidak ditemukan. Perhitungan tidak dapat dilanjutkan.'
            );
        }

        if (count($temps) < self::WINDOW_M || count($humidities) < self::WINDOW_M || count($rainfalls) < self::WINDOW_M) {
            throw new WeatherFetchException(
                'Data cuaca 30 hari dari Open-Meteo tidak lengkap. Perhitungan tidak dapat dilanjutkan.'
            );
        }

        // past_days=14 occupies indices 0..13 (last 7 past days = 7..13).
        // forecast_days=16 occupies indices 14..29 (first 7 forecast days = 14..20).
        $tempWindow = array_slice($temps, 7, self::WINDOW_N);
        $humidityWindow = array_slice($humidities, 7, self::WINDOW_N);
        $rainfallWindow = array_slice($rainfalls, 0, self::WINDOW_M);

        $avgTemp = $this->calculateMean($tempWindow, 'suhu (temperature_2m_mean)');
        $avgHumidity = $this->calculateMean($humidityWindow, 'kelembapan (relative_humidity_2m_mean)');
        $totalRainfall = $this->calculateSum($rainfallWindow, 'curah hujan (precipitation_sum)');

        return [
            'temperature' => $avgTemp,
            'humidity' => $avgHumidity,
            'rainfall' => $totalRainfall,
            'window_n' => self::WINDOW_N,
            'window_m' => self::WINDOW_M,
        ];
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function calculateMean(array $values, string $label): float
    {
        if (count($values) !== self::WINDOW_N) {
            throw new WeatherFetchException("Data {$label} tidak mencukupi 14 hari.");
        }

        $sum = 0.0;
        foreach ($values as $value) {
            if ($value === null || ! is_numeric($value)) {
                throw new WeatherFetchException(
                    "Data {$label} dari Open-Meteo tidak lengkap (mengandung nilai kosong). Perhitungan tidak dapat dilanjutkan."
                );
            }
            $sum += (float) $value;
        }

        return $sum / self::WINDOW_N;
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function calculateSum(array $values, string $label): float
    {
        if (count($values) !== self::WINDOW_M) {
            throw new WeatherFetchException("Data {$label} tidak mencukupi 30 hari.");
        }

        $sum = 0.0;
        foreach ($values as $value) {
            if ($value === null || ! is_numeric($value)) {
                throw new WeatherFetchException(
                    "Data {$label} dari Open-Meteo tidak lengkap (mengandung nilai kosong). Perhitungan tidak dapat dilanjutkan."
                );
            }
            $sum += (float) $value;
        }

        return $sum;
    }
}
