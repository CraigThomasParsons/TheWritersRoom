<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LlmModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LlmModelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeRequest($request);

        $models = LlmModel::query()
            ->enabled()
            ->orderBy('priority')
            ->orderBy('id')
            ->get([
                'provider',
                'model_key',
                'display_name',
                'priority',
                'supports_code',
                'health_status',
            ]);

        return response()->json([
            'models' => $models,
        ]);
    }

    private function authorizeRequest(Request $request): void
    {
        $token = config('services.piper.token');

        if (empty($token)) {
            abort(500, 'Piper token not configured.');
        }

        $provided = $request->bearerToken()
            ?? $request->header('X-Piper-Token');

        if ($provided !== $token) {
            abort(403, 'Invalid token.');
        }
    }
}
