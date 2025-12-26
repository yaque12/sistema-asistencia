<!-- Modal para Crear Usuario -->
<div id="modal-crear-usuario" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <!-- Encabezado del Modal -->
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex items-center justify-between">
            <h3 class="text-xl font-bold">Nuevo Usuario</h3>
            <button id="cerrar-modal-crear" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Cuerpo del Modal -->
        <form id="form-crear-usuario" class="p-6 space-y-4">
            <!-- Nombre de Usuario -->
            <div>
                <label for="crear-nombre-usuario" class="block text-sm font-semibold text-gray-700 mb-1">
                    Nombre de Usuario <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="crear-nombre-usuario" 
                    name="nombre_usuario" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ej: jperez"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-crear-nombre-usuario">Este campo es requerido</p>
            </div>

            <!-- Nombres -->
            <div>
                <label for="crear-nombres" class="block text-sm font-semibold text-gray-700 mb-1">
                    Nombres <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="crear-nombres" 
                    name="nombres" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ej: Juan"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-crear-nombres">Este campo es requerido</p>
            </div>

            <!-- Apellidos -->
            <div>
                <label for="crear-apellidos" class="block text-sm font-semibold text-gray-700 mb-1">
                    Apellidos <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="crear-apellidos" 
                    name="apellidos" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ej: Pérez"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-crear-apellidos">Este campo es requerido</p>
            </div>

            <!-- Departamento de Trabajo -->
            <div>
                <label for="crear-departamento" class="block text-sm font-semibold text-gray-700 mb-1">
                    Departamento de Trabajo
                </label>
                <input 
                    type="text" 
                    id="crear-departamento" 
                    name="departamento_trabajo" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ej: Recursos Humanos"
                >
            </div>

            <!-- Código de Empleado -->
            <div>
                <label for="crear-codigo-empleado" class="block text-sm font-semibold text-gray-700 mb-1">
                    Código de Empleado
                </label>
                <input 
                    type="text" 
                    id="crear-codigo-empleado" 
                    name="codigo_empleado" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ej: EMP001"
                >
            </div>

            <!-- Contraseña -->
            <div>
                <label for="crear-clave" class="block text-sm font-semibold text-gray-700 mb-1">
                    Contraseña <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="crear-clave" 
                    name="clave" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ingrese la contraseña"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-crear-clave">Este campo es requerido</p>
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="crear-confirmar-clave" class="block text-sm font-semibold text-gray-700 mb-1">
                    Confirmar Contraseña <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="crear-confirmar-clave" 
                    name="confirmar_clave" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Confirme la contraseña"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-crear-confirmar-clave">Las contraseñas no coinciden</p>
            </div>

            <!-- Mensaje de éxito/error -->
            <div id="mensaje-crear" class="hidden p-3 rounded-lg"></div>
        </form>

        <!-- Pie del Modal -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end gap-3">
            <button 
                type="button"
                id="cancelar-crear-usuario" 
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
            >
                Cancelar
            </button>
            <button 
                type="submit"
                form="form-crear-usuario"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200 font-semibold"
            >
                Guardar
            </button>
        </div>
    </div>
</div>

