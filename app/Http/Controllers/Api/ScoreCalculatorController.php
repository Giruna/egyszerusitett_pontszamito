<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScoreCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ScoreCalculatorController extends Controller

{
    /**
     * @param Request $request
     * @param ScoreCalculatorService $scoreCalculatorService
     * @return JsonResponse
     */
    public function scoreCalculator(
        Request $request,
        ScoreCalculatorService $scoreCalculatorService
    ): JsonResponse
    {
        try {

            $result = $scoreCalculatorService->handle($request);

            return response()->json($result);

        } catch (Throwable $exception) {

            return response()->json([
                'ok' => false,
                'message' => "Hiba: ".class_basename($exception),
            ], 400);

        }
    }
}
