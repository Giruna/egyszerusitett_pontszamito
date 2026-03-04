<?php

use App\Http\Controllers\Api\ScoreCalculatorController;
use Illuminate\Support\Facades\Route;

Route::post('/score-calculator', [ScoreCalculatorController::class, 'scoreCalculator']);
