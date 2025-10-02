<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Publication;

class PublicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Listar todas las publicaciones activas
    public function index()
    {
        return Publication::where('status', 'activo')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    // Crear una publicación (solo usuarios autenticados con rol seller)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0'
        ]);

        // 1. Validar vehículo contra micro Catálogo
        $catalogResponse = Http::get("http://localhost:8001/api/vehicles/{$validated['vehicle_id']}");

        if ($catalogResponse->failed()) {
            return response()->json(['error' => 'El vehículo no existe en el catálogo'], 400);
        }

        // 2. Crear publicación con el seller autenticado
        $publication = Publication::create([
            'user_id' => $request->user()->id, // seller del Auth MS
            'vehicle_id' => $validated['vehicle_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'status' => 'activo'
        ]);

        return response()->json($publication, 201);
    }

    /**
     * Display the specified resource.
     */
    // Ver una publicación por ID
    public function show($id)
    {
        return Publication::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    // Editar una publicación (solo el seller dueño puede hacerlo)
    public function update(Request $request, $id)
    {
        $publication = Publication::findOrFail($id);

        if ($publication->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $publication->update($request->only(['title', 'description', 'price']));

        return response()->json($publication);
    }

    /**
     * Remove the specified resource from storage.
     */
    // Eliminar publicación (solo seller dueño)
    public function destroy(Request $request, string $id)
    {
        $publication = Publication::findOrFail($id);

        if ($publication->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $publication->delete();

        return response()->json(['message' => 'Publicación eliminada']);
    }

    // Cambiar estado (ej. inactivar o marcar como vendido)
    public function changeStatus(Request $request, $id)
    {
        $publication = Publication::findOrFail($id);

        if ($publication->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'status' => 'required|in:activo,inactivo,vendido'
        ]);

        $publication->status = $request->status;
        $publication->save();

        return response()->json($publication);
    }
}
