<?php

namespace App\Policies;

use App\Models\Reporte;
use App\Models\User;

/**
 * Policy para Reporte
 * 
 * Define las autorizaciones para las acciones relacionadas con reportes.
 * Solo ADMIN y supervisor pueden acceder al módulo de Generar Reporte.
 */
class ReportePolicy
{
    /**
     * Determinar si el usuario puede ver cualquier reporte
     * 
     * Solo ADMIN y supervisor pueden ver reportes
     */
    public function viewAny(User $user): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede ver un reporte específico
     * 
     * Solo ADMIN y supervisor pueden ver reportes
     */
    public function view(User $user, Reporte $reporte): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede crear reportes
     * 
     * Solo ADMIN y supervisor pueden crear reportes
     */
    public function create(User $user): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede actualizar un reporte
     * 
     * Solo ADMIN y supervisor pueden actualizar reportes
     */
    public function update(User $user, Reporte $reporte): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede eliminar un reporte
     * 
     * Solo ADMIN y supervisor pueden eliminar reportes
     */
    public function delete(User $user, Reporte $reporte): bool
    {
        return $user->esAdminOSupervisor();
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

