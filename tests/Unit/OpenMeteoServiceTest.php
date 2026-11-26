<?php

namespace Tests\Unit;

use App\Exceptions\WeatherFetchException;
use App\Services\OpenMeteoService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenMeteoServiceTest extends TestCase
{
    public function test_it_aggregates_14_day_mean_temp_humidity_and_30_day_rainfall_sum(): void
    {
        // Indices 0..6 (7 oldest past days) intentionally differ from indices 7..20 (14-day window)
        // to prove only 7 last past days + 7 first forecast days are averaged.
        $temps = array_merge(
            array_fill(0, 7, 10.0),   // 0..6 excluded from 14-day mean
            [28.2, 28.5, 28.0, 27.4, 27.8, 27.3, 28.3, 29.4, 27.8, 27.6, 28.6, 28.5, 28.3, 27.4], // 7..20 (14 days)
            array_fill(0, 9, 40.0)    // 21..29 excluded from 14-day mean
        );

        $humidities = array_merge(
            array_fill(0, 7, 20.0),
            [79.9, 82.8, 74.6, 78.2, 72.4, 74.8, 72.6, 79.1, 74.9, 81.1, 80.6, 79.3, 69.9, 77.8],
            array_fill(0, 9, 95.0)
        );

        $rainfalls = [
            15.2, 4.5, 5.7, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0,
            6.3, 0.0, 0.0, 7.9, 2.7, 5.8, 15.1, 15.2, 0.0, 14.3,
            11.9, 0.6, 0.0, 40.9, 0.0, 0.0, 0.0, 0.0, 8.3, 0.0,
        ];

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'daily' => [
                    'temperature_2m_mean' => $temps,
                    'relative_humidity_2m_mean' => $humidities,
                    'precipitation_sum' => $rainfalls,
                ],
            ], 200),
        ]);

        $service = new OpenMeteoService;
        $result = $service->fetchAggregatedWeather(-7.2504, 112.7688);

        $this->assertEqualsWithDelta(28.07857142857143, $result['temperature'], 1e-9);
        $this->assertEqualsWithDelta(77.0, $result['humidity'], 1e-9);
        $this->assertEqualsWithDelta(158.4, $result['rainfall'], 1e-9);

        Http::assertSent(function ($request): bool {
            return str_starts_with($request->url(), OpenMeteoService::ENDPOINT)
                && (int) $request['past_days'] === 14
                && (int) $request['forecast_days'] === 16
                && $request['daily'] === 'temperature_2m_mean,relative_humidity_2m_mean,precipitation_sum';
        });
    }

    public function test_it_throws_exception_on_http_error(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['error' => true], 500),
        ]);

        $this->expectException(WeatherFetchException::class);

        (new OpenMeteoService)->fetchAggregatedWeather(-7.2504, 112.7688);
    }

    public function test_it_throws_exception_on_connection_timeout(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $this->expectException(WeatherFetchException::class);

        (new OpenMeteoService)->fetchAggregatedWeather(-7.2504, 112.7688);
    }

    public function test_it_throws_exception_when_daily_data_is_incomplete(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'daily' => [
                    'temperature_2m_mean' => array_fill(0, 29, 28.0),
                    'relative_humidity_2m_mean' => array_fill(0, 30, 77.0),
                    'precipitation_sum' => array_fill(0, 30, 5.0),
                ],
            ], 200),
        ]);

        $this->expectException(WeatherFetchException::class);

        (new OpenMeteoService)->fetchAggregatedWeather(-7.2504, 112.7688);
    }

    public function test_it_tolerates_trailing_forecast_horizon_null_on_day_30(): void
    {
        $temps = array_fill(0, 29, 27.5);
        $temps[] = null;

        $humidities = array_fill(0, 29, 85.0);
        $humidities[] = null;

        $rainfalls = array_fill(0, 29, 10.0);
        $rainfalls[] = null;

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'daily' => [
                    'temperature_2m_mean' => $temps,
                    'relative_humidity_2m_mean' => $humidities,
                    'precipitation_sum' => $rainfalls,
                ],
            ], 200),
        ]);

        $result = (new OpenMeteoService)->fetchAggregatedWeather(-5.1679, 119.4336);

        $this->assertEqualsWithDelta(27.5, $result['temperature'], 1e-9);
        $this->assertEqualsWithDelta(85.0, $result['humidity'], 1e-9);
        $this->assertEqualsWithDelta(300.0, $result['rainfall'], 1e-9);
    }
}
