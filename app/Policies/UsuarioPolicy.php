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
     * Solo ADMIN y supervisor pueden ver la lista de usuarios
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede crear nuevos usuarios
     * 
     * Solo ADMIN y supervisor pueden crear usuarios
     * 
     * @param User $user El usuario autenticado
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede actualizar un usuario específico
     * 
     * Solo ADMIN y supervisor pueden actualizar usuarios
     * 
     * @param User $user El usuario autenticado
     * @param User $usuario El usuario que se desea actualizar
     * @return bool
     */
    public function update(User $user, User $usuario): bool
    {
        return $user->esAdminOSupervisor();
    }

    /**
     * Determinar si el usuario puede eliminar un usuario específico
     * 
     * Solo ADMIN y supervisor pueden eliminar usuarios
     * Los usuarios no pueden eliminarse a sí mismos
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

        return $user->esAdminOSupervisor();
    }
}

