<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe errores no controlados del frontend para dejarlos en el log del backend
 * junto con el request_id y el usuario.
 */
class ClientLogController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'stack' => 'nullable|string|max:5000',
            'url' => 'nullable|string|max:2000',
        ]);

        Log::error('client.error', [
            'message' => $data['message'],
            'url' => $data['url'] ?? null,
            'stack' => $data['stack'] ?? null,
            'user_id' => optional($request->user())->id,
        ]);

        return response()->json(['ok' => true]);
    }
}
