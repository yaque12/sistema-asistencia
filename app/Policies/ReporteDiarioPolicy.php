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
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver reportes diarios
        return true;
    }

    /**
     * Determinar si el usuario puede ver un reporte diario específico
     */
    public function view(User $user, ReporteDiario $reporteDiario): bool
    {
        // Todos los usuarios autenticados pueden ver cualquier reporte diario
        return true;
    }

    /**
     * Determinar si el usuario puede crear reportes diarios
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear reportes diarios
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar un reporte diario
     */
    public function update(User $user, ReporteDiario $reporteDiario): bool
    {
        // Todos los usuarios autenticados pueden actualizar cualquier reporte diario
        return true;
    }

    /**
     * Determinar si el usuario puede eliminar un reporte diario
     */
    public function delete(User $user, ReporteDiario $reporteDiario): bool
    {
        // Todos los usuarios autenticados pueden eliminar cualquier reporte diario
        return true;
    }

    /**
     * Determinar si el usuario puede restaurar un reporte diario eliminado
     */
    public function restore(User $user, ReporteDiario $reporteDiario): bool
    {
        // Todos los usuarios autenticados pueden restaurar reportes diarios
        return true;
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente un reporte diario
     */
    public function forceDelete(User $user, ReporteDiario $reporteDiario): bool
    {
        // Todos los usuarios autenticados pueden eliminar permanentemente reportes diarios
        return true;
    }
}

