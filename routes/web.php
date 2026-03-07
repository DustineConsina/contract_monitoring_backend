<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'application' => 'PFDA Contract Monitoring System',
        'status' => 'running',
        'api_endpoint' => '/api',
        'version' => '1.0'
    ]);
});
