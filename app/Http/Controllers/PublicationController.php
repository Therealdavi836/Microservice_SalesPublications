<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publication;
use Illuminate\Support\Facades\Http;

class PublicationController extends Controller
{
    // Helper para validar usuario en Auth MS
    private function getAuthenticatedUser($request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        $authResponse = Http::withToken($token)->get("http://localhost:8000/api/me");

        if ($authResponse->failed()) {
            return null;
        }

        return $authResponse->json(); // { id, name, email, role }
    }

    // Listar todas las publicaciones activas (opcional: filtrar por usuario)
    public function index(Request $request)
    {
        if ($request->has('user_id')) {
            return Publication::where('status', 'activo')
                ->where('user_id', $request->query('user_id'))
                ->get();
        }

        return Publication::where('status', 'activo')->get();
    }

    // Crear una publicación (solo usuarios con rol seller)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'   => 'required|string',
            'title'        => 'required|string',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0'
        ]);

        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if ($user['role'] !== 'seller') {
            return response()->json(['error' => 'Solo vendedores pueden publicar'], 403);
        }

        // Validar vehículo contra Catálogo MS
        $catalogResponse = Http::get("http://localhost:8001/vehicles/{$validated['vehicle_id']}");
        if ($catalogResponse->failed()) {
            return response()->json(['error' => 'El vehículo no existe en el catálogo'], 400);
        }

        $publication = Publication::create([
            'user_id'    => $user['id'],
            'vehicle_id' => $validated['vehicle_id'],
            'title'      => $validated['title'],
            'description'=> $validated['description'] ?? null,
            'price'      => $validated['price'],
            'status'     => 'activo'
        ]);

        Http::post('http://127.0.0.1:8003/api/notifications/', [
            'user_id' => $publication->user_id,
            'title' => 'Publicación creada',
            'message' => 'Tu vehículo fue publicado exitosamente y ahora está visible en el catálogo.',
            'type' => 'success'
        ]);

        return response()->json($publication, 201);
    }

    // Ver una publicación por ID
    public function show($id)
    {
        return Publication::findOrFail($id);
    }

    // Editar publicación (solo seller dueño puede hacerlo)
    public function update(Request $request, $id)
    {
        $publication = Publication::findOrFail($id);

        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if ($user['role'] !== 'seller' || $publication->user_id !== $user['id']) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $publication->update($request->only(['title', 'description', 'price']));

        return response()->json($publication);
    }

    // Eliminar publicación (solo seller dueño)
    public function destroy(Request $request, string $id)
    {
        $publication = Publication::findOrFail($id);

        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if ($user['role'] !== 'seller' || $publication->user_id !== $user['id']) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $publication->delete();

        return response()->json(['message' => 'Publicación eliminada']);
    }

    // Cambiar estado (ej. inactivar o marcar como vendido)
    public function changeStatus(Request $request, $id)
    {
        $publication = Publication::findOrFail($id);

        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if ($user['role'] !== 'seller' || $publication->user_id !== $user['id']) {
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
