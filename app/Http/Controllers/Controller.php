<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        title: "VSTR Management System API",
        version: "1.0.0",
        description: "Visitor Management System API Documentation"
    ),
    OA\Server(
        url: "http://localhost:8000/api",
        description: "Local Server"
    ),
    OA\Server(
        url: "https://api.vstrmgmt.com",
        description: "Production Server"
    ),
    OA\Server(
        url: "https://staging-api.vstrmgmt.com",
        description: "Staging Server"
    ),
    OA\SecurityScheme(
        securityScheme: "bearerAuth",
        type: "http",
        scheme: "bearer",
        bearerFormat: "JWT"
    )
]

abstract class Controller
{
    //
}
