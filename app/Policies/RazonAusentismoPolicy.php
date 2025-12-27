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
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Por ahora, todos los usuarios autenticados pueden ver la lista
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }

    /**
     * Determinar si el usuario puede crear nuevas razones de ausentismos
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function create(User $user): bool
    {
        // Por ahora, todos los usuarios autenticados pueden crear razones de ausentismos
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar una razón de ausentismo específica
     * 
     * @param User $user El usuario autenticado
     * @param RazonAusentismo $razonAusentismo La razón de ausentismo que se desea actualizar
     * @return bool
     */
    public function update(User $user, RazonAusentismo $razonAusentismo): bool
    {
        // Por ahora, todos los usuarios autenticados pueden actualizar cualquier razón de ausentismo
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }

    /**
     * Determinar si el usuario puede eliminar una razón de ausentismo específica
     * 
     * @param User $user El usuario autenticado
     * @param RazonAusentismo $razonAusentismo La razón de ausentismo que se desea eliminar
     * @return bool
     */
    public function delete(User $user, RazonAusentismo $razonAusentismo): bool
    {
        // Por ahora, todos los usuarios autenticados pueden eliminar razones de ausentismos
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }
}

