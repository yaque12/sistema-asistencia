<?php

namespace App\Policies;

use App\Models\ReporteDiario;
use App\Models\User;

/**
 * Policy para ReporteDiario
 * 
 * Define las autorizaciones para las acciones relacionadas con reportes diarios.
 * Por ahora, todos los usuarios autenticados pueden realizar todas las acciones.
 */
class ReporteDiarioPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier reporte diario
     * 
     * ADMIN, supervisor y RRHH.PLAN pueden ver reportes diarios
     */
    public function viewAny(User $user): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('RRHH.PLAN');
    }

    /**
     * Determinar si el usuario puede ver un reporte diario específico
     * 
     * ADMIN, supervisor y RRHH.PLAN pueden ver reportes diarios
     */
    public function view(User $user, ReporteDiario $reporteDiario): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('RRHH.PLAN');
    }

    /**
     * Determinar si el usuario puede crear reportes diarios
     * 
     * ADMIN, supervisor y RRHH.PLAN pueden crear reportes diarios
     */
    public function create(User $user): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('RRHH.PLAN');
    }

    /**
     * Determinar si el usuario puede actualizar un reporte diario
     * 
     * ADMIN, supervisor y RRHH.PLAN pueden actualizar reportes diarios
     */
    public function update(User $user, ReporteDiario $reporteDiario): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('RRHH.PLAN');
    }

    /**
     * Determinar si el usuario puede eliminar un reporte diario
     * 
     * Solo ADMIN y supervisor pueden eliminar reportes diarios
     * RRHH.PLAN NO puede eliminar
     */
    public function delete(User $user, ReporteDiario $reporteDiario): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede restaurar un reporte diario eliminado
     * 
     * Solo ADMIN y supervisor pueden restaurar reportes diarios
     */
    public function restore(User $user, ReporteDiario $reporteDiario): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente un reporte diario
     * 
     * Solo ADMIN y supervisor pueden eliminar permanentemente reportes diarios
     */
    public function forceDelete(User $user, ReporteDiario $reporteDiario): bool
    {
        return $user->esAdminOSupervisor();
    }
}

