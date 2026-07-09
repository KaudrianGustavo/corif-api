<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/ping',
        summary: 'Verifica se a API está no ar',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API operacional',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                    ]
                )
            ),
        ]
    )]
    public function ping(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
