<!-- Modal para Editar Empleado -->
<div id="modal-editar-empleado" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <!-- Encabezado del Modal -->
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex items-center justify-between">
            <h3 class="text-xl font-bold">Editar Empleado</h3>
            <button id="cerrar-modal-editar" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Cuerpo del Modal -->
        <form id="form-editar-empleado" class="p-6 space-y-4">
            <input type="hidden" id="editar-id-empleado" name="id_empleado">
            
            <!-- Nombres -->
            <div>
                <label for="editar-nombres" class="block text-sm font-semibold text-gray-700 mb-1">
                    Nombres <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="editar-nombres" 
                    name="nombres" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-editar-nombres">Este campo es requerido</p>
            </div>

            <!-- Apellidos -->
            <div>
                <label for="editar-apellidos" class="block text-sm font-semibold text-gray-700 mb-1">
                    Apellidos <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="editar-apellidos" 
                    name="apellidos" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-editar-apellidos">Este campo es requerido</p>
            </div>

            <!-- Departamento -->
            <div>
                <label for="editar-departamento" class="block text-sm font-semibold text-gray-700 mb-1">
                    Departamento
                </label>
                <input 
                    type="text" 
                    id="editar-departamento" 
                    name="departamento" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <!-- Código Empleado -->
            <div>
                <label for="editar-codigo-empleado" class="block text-sm font-semibold text-gray-700 mb-1">
                    Código Empleado
                </label>
                <input 
                    type="text" 
                    id="editar-codigo-empleado" 
                    name="codigo_empleado" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <!-- Fecha de Ingreso -->
            <div>
                <label for="editar-fecha-ingreso" class="block text-sm font-semibold text-gray-700 mb-1">
                    Fecha de Ingreso <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="editar-fecha-ingreso" 
                    name="fecha_ingreso" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <p class="text-red-500 text-xs mt-1 hidden" id="error-editar-fecha-ingreso">Este campo es requerido</p>
            </div>

            <!-- Mensaje de éxito/error -->
            <div id="mensaje-editar" class="hidden p-3 rounded-lg"></div>
        </form>

        <!-- Pie del Modal -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end gap-3">
            <button 
                type="button"
                id="cancelar-editar-empleado" 
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
            >
                Cancelar
            </button>
            <button 
                type="submit"
                form="form-editar-empleado"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200 font-semibold"
            >
                Actualizar
            </button>
        </div>
    </div>
</div>

