<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login de usuario
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'contraseña' => 'required|string'
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->contraseña, $usuario->contraseña)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Obtener información del rol y datos adicionales
        $rol = $usuario->rol;
        $datosAdicionales = null;

        // Obtener datos específicos según el rol
        switch ($rol->nombre_rol) {
            case 'Cliente':
                $datosAdicionales = Cliente::where('id_usuario', $usuario->id_usuario)->first();
                break;
            case 'Empleado':
                $datosAdicionales = Empleado::where('id_usuario', $usuario->id_usuario)->first();
                break;
            case 'Administrador':
                $datosAdicionales = Administrador::where('id_usuario', $usuario->id_usuario)->first();
                break;
        }

        // Crear token con información del rol
        $token = $usuario->createToken('auth-token', [$rol->nombre_rol])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'usuario' => [
                    'id' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'rol' => $rol->nombre_rol,
                    'datos_adicionales' => $datosAdicionales
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Registro de usuario (solo clientes)
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido_paterno' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido_materno' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'email' => 'required|email|unique:usuarios,email|regex:/^[a-zA-Z@._-]+$/',
            'contraseña' => 'required|string|min:6',
            'telefono' => 'required|string|max:20'
        ], [
            'nombre.regex' => 'El nombre no puede contener números',
            'apellido_paterno.regex' => 'El apellido paterno no puede contener números',
            'apellido_materno.regex' => 'El apellido materno no puede contener números',
            'email.regex' => 'El email no puede contener números'
        ]);

        // Obtener el rol de Cliente
        $rolCliente = \App\Models\Rol::where('nombre_rol', 'Cliente')->first();
        
        if (!$rolCliente) {
            return response()->json([
                'success' => false,
                'message' => 'Error: Rol de Cliente no encontrado'
            ], 500);
        }

        // Crear usuario
        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'nombre_usuario' => $request->nombre_usuario,
            'email' => $request->email,
            'contraseña' => Hash::make($request->contraseña),
            'id_rol' => $rolCliente->id_rol,
            'telefono' => $request->telefono,
            'fecha_registro' => now()
        ]);

        // Crear cliente con dirección por defecto
        $cliente = Cliente::create([
            'id_usuario' => $usuario->id_usuario,
            'direccion' => 'Dirección por definir', // Dirección por defecto
            'telefono' => $request->telefono,
            'fecha_registro' => now()
        ]);

        // Crear token
        $token = $usuario->createToken('auth-token', ['Cliente'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'usuario' => [
                    'id' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'rol' => 'Cliente',
                    'datos_adicionales' => $cliente
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    /**
     * Logout de usuario
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $rol = $usuario->rol;
        $datosAdicionales = null;

        // Obtener datos específicos según el rol
        switch ($rol->nombre_rol) {
            case 'Cliente':
                $datosAdicionales = Cliente::where('id_usuario', $usuario->id_usuario)->first();
                break;
            case 'Empleado':
                $datosAdicionales = Empleado::where('id_usuario', $usuario->id_usuario)->first();
                break;
            case 'Administrador':
                $datosAdicionales = Administrador::where('id_usuario', $usuario->id_usuario)->first();
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'usuario' => [
                    'id' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'rol' => $rol->nombre_rol,
                    'datos_adicionales' => $datosAdicionales
                ]
            ]
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        $usuario = $request->user();

        if (!Hash::check($request->current_password, $usuario->contraseña)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        $usuario->update([
            'contraseña' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña cambiada exitosamente'
        ]);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(Request $request, string $role): JsonResponse
    {
        $usuario = $request->user();
        $hasRole = $usuario->rol->nombre_rol === $role;

        return response()->json([
            'success' => true,
            'data' => [
                'has_role' => $hasRole,
                'user_role' => $usuario->rol->nombre_rol,
                'required_role' => $role
            ]
        ]);
    }

    /**
     * Obtener permisos del usuario
     */
    public function getPermissions(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $rol = $usuario->rol->nombre_rol;

        $permissions = [
            'Cliente' => [
                'ver_componentes',
                'crear_armados',
                'ver_cotizaciones',
                'ver_historial_compras'
            ],
            'Empleado' => [
                'ver_componentes',
                'gestionar_ventas',
                'gestionar_devoluciones',
                'ver_reportes_basicos',
                'gestionar_armados'
            ],
            'Administrador' => [
                'ver_componentes',
                'gestionar_ventas',
                'gestionar_devoluciones',
                'generar_reportes',
                'gestionar_usuarios',
                'gestionar_inventario',
                'gestionar_armados',
                'acceso_completo'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'rol' => $rol,
                'permisos' => $permissions[$rol] ?? []
            ]
        ]);
    }
} 