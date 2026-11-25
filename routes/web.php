<?php

use App\Http\Controllers\CropRecommendationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CropRecommendationController::class, 'index'])->name('home');
Route::get('/weather', [CropRecommendationController::class, 'weather'])->name('weather.preview');
Route::post('/recommend', [CropRecommendationController::class, 'recommend'])->name('recommend.store');
Route::get('/recommend', static fn () => redirect()->route('home'));
