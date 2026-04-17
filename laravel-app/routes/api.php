<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

Route::get('/whatsapp-status', function () {
    try {
        $response = Http::get('http://whatsapp_go_api:8080/api/status');
        return $response->json();
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Serviço Go offline']);
    }
});

// NOVA ROTA: Ponte para envio de mensagem
Route::post('/send-message', function (Request $request) {
    // Valida se enviaram o telefone e a mensagem
    $request->validate([
        'phone' => 'required|string',
        'message' => 'required|string',
    ]);

    try {
        // O Laravel pega os dados e envia (POST) para o nosso Go!
        $response = Http::post('http://whatsapp_go_api:8080/api/send', [
            'phone' => $request->phone,
            'message' => $request->message,
        ]);

        return $response->json();
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Falha ao conectar no Go']);
    }
});