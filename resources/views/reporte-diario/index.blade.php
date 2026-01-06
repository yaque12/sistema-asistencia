@extends('layouts.app')

@section('title', 'Reporte Diario - Sistema de Asistencia')

@push('styles')
    <style>
        /* Estilos adicionales si es necesario */
    </style>
@endpush

@section('content')
    
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reporte Diario</h1>
        <p class="text-gray-600 mt-2">Ingresa la asistencia diaria de los empleados</p>
    </div>

    <!-- Contenedor para mensajes globales -->
    <div id="mensaje-global" class="hidden mb-6 p-4 rounded-lg"></div>

    <!-- Selectores de fecha y departamento -->
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full md:w-auto">
                <label for="fecha-reporte" class="block text-sm font-semibold text-gray-700 mb-2">
                    Fecha del Reporte <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="fecha-reporte" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                >
            </div>
            <div class="flex-1 w-full md:w-auto">
                <label for="departamento-reporte" class="block text-sm font-semibold text-gray-700 mb-2">
                    Departamento
                </label>
                <select 
                    id="departamento-reporte" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Cargando...</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla de empleados -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código de Empleado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombres</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellidos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Trabajadas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Ausentes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razón de Ausencias</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-empleados-body" class="bg-white divide-y divide-gray-200">
                    <!-- Los empleados se cargarán dinámicamente desde JavaScript -->
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            Seleccione una fecha para cargar los empleados
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje cuando no hay empleados -->
        <div id="sin-empleados" class="hidden p-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-medium">No se encontraron empleados</p>
            <p class="text-sm mt-1" id="mensaje-sin-empleados">No hay empleados registrados en el sistema</p>
        </div>
    </div>

    <!-- Botón Guardar -->
    <div class="flex justify-end">
        <button 
            id="btn-guardar-reporte"
            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 shadow-md flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Guardar Reporte
        </button>
    </div>

@endsection

@push('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/reporte-diario.js'])
    @else
        <!-- Fallback: JavaScript inline para desarrollo -->
        @php
            $jsPath = resource_path('js/reporte-diario.js');
            $jsContent = file_exists($jsPath) ? file_get_contents($jsPath) : '';
        @endphp
        @if($jsContent)
            <script>
                // Pasar roles del usuario a JavaScript
                window.usuarioRoles = @json($rolesUsuario ?? []);
                {!! $jsContent !!}
            </script>
        @endif
    @endif
@endpush

