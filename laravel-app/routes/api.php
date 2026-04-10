<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/whatsapp-status', function () {
    try {
        $response = Http::get('http://whatsapp_go_api:8080/api/status');
        return $response->json();
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Serviço Go offline']);
    }
});