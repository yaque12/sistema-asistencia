<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RazonAusentismo;

/**
 * Policy para gestionar la autorización de acciones sobre razones de ausentismos
 * 
 * Esta Policy controla quién puede realizar qué acciones
 * sobre las razones de ausentismos del sistema.
 */
class RazonAusentismoPolicy
{
    /**
     * Determinar si el usuario puede ver la lista de razones de ausentismos
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden ver razones de ausentismos
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }

    /**
     * Determinar si el usuario puede crear nuevas razones de ausentismos
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden crear razones de ausentismos
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }

    /**
     * Determinar si el usuario puede actualizar una razón de ausentismo específica
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden actualizar razones de ausentismos
     * 
     * @param User $user El usuario autenticado
     * @param RazonAusentismo $razonAusentismo La razón de ausentismo que se desea actualizar
     * @return bool
     */
    public function update(User $user, RazonAusentismo $razonAusentismo): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }

    /**
     * Determinar si el usuario puede eliminar una razón de ausentismo específica
     * 
     * ADMIN, supervisor y gerenciacontable01 pueden eliminar razones de ausentismos
     * 
     * @param User $user El usuario autenticado
     * @param RazonAusentismo $razonAusentismo La razón de ausentismo que se desea eliminar
     * @return bool
     */
    public function delete(User $user, RazonAusentismo $razonAusentismo): bool
    {
        return $user->esAdminOSupervisor() || $user->tieneRol('gerenciacontable01');
    }
}

