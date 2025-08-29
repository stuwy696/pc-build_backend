<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        return Rol::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_rol' => 'required|in:Cliente,Empleado,Administrador',
            'descripcion' => 'nullable|string',
        ]);

        return Rol::create($request->all());
    }

    public function show($id)
    {
        return Rol::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);
        $request->validate([
            'nombre_rol' => 'required|in:Cliente,Empleado,Administrador',
            'descripcion' => 'nullable|string',
        ]);

        $rol->update($request->all());
        return $rol;
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();
        return response()->json(null, 204);
    }
}