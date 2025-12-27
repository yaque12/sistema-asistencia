<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy para gestionar la autorización de acciones sobre usuarios
 * 
 * Esta Policy controla quién puede realizar qué acciones
 * sobre los usuarios del sistema.
 */
class UsuarioPolicy
{
    /**
     * Determinar si el usuario puede ver la lista de usuarios
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
     * Determinar si el usuario puede crear nuevos usuarios
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function create(User $user): bool
    {
        // Por ahora, todos los usuarios autenticados pueden crear usuarios
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar un usuario específico
     * 
     * @param User $user El usuario autenticado
     * @param User $usuario El usuario que se desea actualizar
     * @return bool
     */
    public function update(User $user, User $usuario): bool
    {
        // Por ahora, todos los usuarios autenticados pueden actualizar cualquier usuario
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }

    /**
     * Determinar si el usuario puede eliminar un usuario específico
     * 
     * @param User $user El usuario autenticado
     * @param User $usuario El usuario que se desea eliminar
     * @return bool
     */
    public function delete(User $user, User $usuario): bool
    {
        // Los usuarios no pueden eliminarse a sí mismos
        if ($user->id_usuario === $usuario->id_usuario) {
            return false;
        }

        // Por ahora, todos los usuarios autenticados pueden eliminar otros usuarios
        // Se puede modificar para agregar restricciones según roles o permisos
        return true;
    }
}

