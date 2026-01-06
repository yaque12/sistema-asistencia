<?php

namespace App\Policies;

use App\Models\Reporte;
use App\Models\User;

/**
 * Policy para Reporte
 * 
 * Define las autorizaciones para las acciones relacionadas con reportes.
 * Por ahora, todos los usuarios autenticados pueden realizar todas las acciones.
 */
class ReportePolicy
{
    /**
     * Determinar si el usuario puede ver cualquier reporte
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver reportes
        return true;
    }

    /**
     * Determinar si el usuario puede ver un reporte específico
     */
    public function view(User $user, Reporte $reporte): bool
    {
        // Todos los usuarios autenticados pueden ver cualquier reporte
        return true;
    }

    /**
     * Determinar si el usuario puede crear reportes
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear reportes
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar un reporte
     */
    public function update(User $user, Reporte $reporte): bool
    {
        // Todos los usuarios autenticados pueden actualizar cualquier reporte
        return true;
    }

    /**
     * Determinar si el usuario puede eliminar un reporte
     */
    public function delete(User $user, Reporte $reporte): bool
    {
        // Todos los usuarios autenticados pueden eliminar cualquier reporte
        return true;
    }

    /**
     * Determinar si el usuario puede restaurar un reporte eliminado
     */
    public function restore(User $user, Reporte $reporte): bool
    {
        // Todos los usuarios autenticados pueden restaurar reportes
        return true;
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente un reporte
     */
    public function forceDelete(User $user, Reporte $reporte): bool
    {
        // Todos los usuarios autenticados pueden eliminar permanentemente reportes
        return true;
    }
}

