<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Empleado;

/**
 * Policy para gestionar la autorización de acciones sobre empleados
 * 
 * Esta Policy controla quién puede realizar qué acciones
 * sobre los empleados del sistema.
 */
class EmpleadoPolicy
{
    /**
     * Determinar si el usuario puede ver la lista de empleados
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden ver empleados
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }

    /**
     * Determinar si el usuario puede crear nuevos empleados
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden crear empleados
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }

    /**
     * Determinar si el usuario puede actualizar un empleado específico
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden actualizar empleados
     * 
     * @param User $user El usuario autenticado
     * @param Empleado $empleado El empleado que se desea actualizar
     * @return bool
     */
    public function update(User $user, Empleado $empleado): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }

    /**
     * Determinar si el usuario puede eliminar un empleado específico
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden eliminar empleados
     * 
     * @param User $user El usuario autenticado
     * @param Empleado $empleado El empleado que se desea eliminar
     * @return bool
     */
    public function delete(User $user, Empleado $empleado): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }
}

