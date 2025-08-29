<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::with('sedes')->select('id', 'nombre', 'logo')
            ->paginate(15);

        $clientes->getCollection()->transform(function ($cliente) {
            return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'logo' => $cliente->logo,
                    'sedes' => $cliente->sedes->map(function ($sede) {
                        return [
                                'id' => $sede->id,
                                'nombre' => $sede->nombre,
                                'direccion'=> $sede->direccion,
                                'telefono'=> $sede->telefono,
                                'email'=> $sede->email
                        ];
                    }),
                ];
            });
        
        return response()->json([
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
                'next_page_url' => $clientes->nextPageUrl(),
                'prev_page_url' => $clientes->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::with('sedes')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'logo' => $cliente->logo,
                    'sedes' => $cliente->sedes->map(function ($sede) {
                        return [
                                'id' => $sede->id,
                                'nombre' => $sede->nombre,
                                'direccion'=> $sede->direccion,
                                'telefono'=> $sede->telefono,
                                'email'=> $sede->email
                        ];
                    }),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
