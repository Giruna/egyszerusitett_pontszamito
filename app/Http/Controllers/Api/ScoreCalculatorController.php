<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScoreCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery\Exception;

class ScoreCalculatorController extends Controller

{
    /**
     * @param Request $request
     * @param ScoreCalculatorService $scoreCalculatorService
     * @return JsonResponse
     */
    public function scoreCalculator(Request $request, ScoreCalculatorService $scoreCalculatorService): JsonResponse
    {
        try {
            $result = $scoreCalculatorService->handle($request);
        } catch (Exception $exception) {
//            dd($exception);
//            return [
//                'ok' => false,
//                'message' => "hiba",
//            ];
        }

        return response()->json($result, $result['status'] ?? 200);
    }
}
