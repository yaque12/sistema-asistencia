<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy para gestionar la autorización de acciones sobre consultas y descargas
 * 
 * Esta Policy controla quién puede realizar qué acciones
 * sobre las consultas y descargas del sistema.
 */
class ConsultaDescargaPolicy
{
    /**
     * Determinar si el usuario puede ver la vista de consultas y descargas
     * 
     * ADMIN, supervisor, gerenciacontable01 y RRHH.PLAN pueden ver consultas y descargas
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->esAdminOSupervisor() || 
               $user->tieneRol('gerenciacontable01') || 
               $user->tieneRol('RRHH.PLAN');
    }

    /**
     * Determinar si el usuario puede consultar reportes
     * 
     * ADMIN, supervisor, gerenciacontable01 y RRHH.PLAN pueden consultar
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function consultar(User $user): bool
    {
        return $user->esAdminOSupervisor() || 
               $user->tieneRol('gerenciacontable01') || 
               $user->tieneRol('RRHH.PLAN');
    }

    /**
     * Determinar si el usuario puede descargar reportes
     * 
     * ADMIN, supervisor, gerenciacontable01 y RRHH.PLAN pueden descargar
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function descargar(User $user): bool
    {
        return $user->esAdminOSupervisor() || 
               $user->tieneRol('gerenciacontable01') || 
               $user->tieneRol('RRHH.PLAN');
    }
}

