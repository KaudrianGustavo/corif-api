<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuloRequest;
use App\Models\Curso;
use App\Services\ModuloService;
use Illuminate\Http\JsonResponse;

class ModuloController extends Controller{
    
    public function __construct(private ModuloService $moduloService) {}

    public function store(StoreModuloRequest $request, Curso $curso): JsonResponse {
        $modulo = $this->moduloService->criar($curso, $request->validated());

        return response()->json($modulo, 201);
    }
}
