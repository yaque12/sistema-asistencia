<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

/**
 * Controlador para obtener información de roles
 * 
 * Este controlador proporciona endpoints para obtener
 * la lista de roles disponibles en el sistema.
 */
class RolController extends Controller
{
    /**
     * Obtener todos los roles disponibles
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('nombre_rol', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
            ],
        ]);
    }
}

