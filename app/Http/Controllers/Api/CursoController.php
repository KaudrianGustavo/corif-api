<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCursoRequest;
use App\Services\CursoService;
use Illuminate\Http\JsonResponse;
class CursoController extends Controller {
    public function __construct(private CursoService $cursoService) {}

    public function store(StoreCursoRequest $request): JsonResponse {
        $curso = $this->cursoService->criar($request->validated(), $request->user());

        return response()->json($curso, 201);
    }
}
