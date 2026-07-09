<?php

namespace App\Http\Controllers\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'CORIF API',
    description: 'API REST da plataforma de ensino CORIF (vocologia, canto e regência coral).'
)]
#[OA\Server(
    url: 'https://corif.api',
    description: 'Ambiente local'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token'
)]
class OpenApiSpec
{
}