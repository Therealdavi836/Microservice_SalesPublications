<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Publication;

class SaleController extends Controller
{
    // Listar ventas (puede ser restringido según rol)
    public function index()
    {
        return Sale::with('publication')->get();
    }

    // Registrar una venta
    public function store(Request $request)
    {
        $validated = $request->validate([
            'publication_id' => 'required|exists:publications,id',
            'sale_price' => 'required|numeric|min:0'
        ]);

        $publication = Publication::findOrFail($validated['publication_id']);

        // 1. Verificar que la publicación siga activa
        if ($publication->status !== 'activo') {
            return response()->json(['error' => 'La publicación no está disponible'], 400);
        }

        // 2. Registrar la venta
        $sale = Sale::create([
            'publication_id' => $publication->id,
            'customer_id' => $request->user()->id, // customer desde Auth MS
            'seller_id' => $publication->user_id,  // seller desde publicación
            'sale_price' => $validated['sale_price'],
            'sale_date' => now()
        ]);

        // 3. Cambiar estado de publicación
        $publication->status = 'vendido';
        $publication->save();

        return response()->json($sale, 201);
    }

    // Ver detalle de una venta
    public function show($id)
    {
        return Sale::with('publication')->findOrFail($id);
    }
}
