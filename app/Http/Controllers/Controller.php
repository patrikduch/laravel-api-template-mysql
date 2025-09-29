<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Laravel API",
 *     description="API documentation"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
