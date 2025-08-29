<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $usuarios = Usuario::with('rol')->get();
        
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de usuario'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido_paterno' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido_materno' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'email' => 'required|email|unique:usuarios,email|regex:/^[a-zA-Z@._-]+$/',
            'contraseña' => 'required|string|min:6',
            'id_rol' => 'required|exists:roles,id_rol',
            'telefono' => 'nullable|string|max:20'
        ], [
            'nombre.regex' => 'El nombre no puede contener números',
            'apellido_paterno.regex' => 'El apellido paterno no puede contener números',
            'apellido_materno.regex' => 'El apellido materno no puede contener números',
            'email.regex' => 'El email no puede contener números'
        ]);

        $data = $request->all();
        $data['contraseña'] = \Hash::make($data['contraseña']);

        $usuario = Usuario::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido_paterno' => 'sometimes|required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido_materno' => 'sometimes|required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'nombre_usuario' => 'sometimes|required|string|max:50|unique:usuarios,nombre_usuario,' . $id . ',id_usuario',
            'email' => 'sometimes|required|email|unique:usuarios,email,' . $id . ',id_usuario|regex:/^[a-zA-Z@._-]+$/',
            'contraseña' => 'sometimes|required|string|min:6',
            'id_rol' => 'sometimes|required|exists:roles,id_rol',
            'telefono' => 'nullable|string|max:20'
        ], [
            'nombre.regex' => 'El nombre no puede contener números',
            'apellido_paterno.regex' => 'El apellido paterno no puede contener números',
            'apellido_materno.regex' => 'El apellido materno no puede contener números',
            'email.regex' => 'El email no puede contener números'
        ]);

        $usuario = Usuario::findOrFail($id);
        $data = $request->all();
        if (!empty($data['contraseña'])) {
            $data['contraseña'] = \Hash::make($data['contraseña']);
        } else {
            unset($data['contraseña']);
        }

        $usuario->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }
}
