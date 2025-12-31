@extends('layouts.app')

@section('title', 'Consultas y Descargas - Sistema de Asistencia')

@push('styles')
    <style>
        /* Estilos adicionales si es necesario */
    </style>
@endpush

@section('content')
    
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Consultas y Descargas</h1>
        <p class="text-gray-600 mt-2">Consulta y descarga reportes de asistencia por rango de fechas y departamento</p>
    </div>

    <!-- Contenedor para mensajes globales -->
    <div id="mensaje-global" class="hidden mb-6 p-4 rounded-lg"></div>

    <!-- Formulario de filtros -->
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filtros de Búsqueda</h2>
        <form id="form-filtros" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Campo Fecha Desde -->
                <div>
                    <label for="fecha-desde" class="block text-sm font-semibold text-gray-700 mb-2">
                        Fecha Desde <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha-desde" 
                        name="fecha_desde"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                    <span id="error-fecha-desde" class="hidden text-red-500 text-sm mt-1"></span>
                </div>

                <!-- Campo Fecha Hasta -->
                <div>
                    <label for="fecha-hasta" class="block text-sm font-semibold text-gray-700 mb-2">
                        Fecha Hasta <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha-hasta" 
                        name="fecha_hasta"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                    <span id="error-fecha-hasta" class="hidden text-red-500 text-sm mt-1"></span>
                </div>

                <!-- Selector de Departamento -->
                <div>
                    <label for="departamento-filtro" class="block text-sm font-semibold text-gray-700 mb-2">
                        Departamento
                    </label>
                    <select 
                        id="departamento-filtro" 
                        name="departamento"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="todos">Todos</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <button 
                    type="button"
                    id="btn-consultar"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 shadow-md flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Consultar
                </button>
                
                <button 
                    type="button"
                    id="btn-descargar"
                    class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 shadow-md flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Descargar
                </button>
            </div>
        </form>
    </div>

    <!-- Área de resultados -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Resultados de la Consulta</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código Empleado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombres</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellidos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Trabajadas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Ausentes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razón de Ausencias</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
                    </tr>
                </thead>
                <tbody id="tabla-resultados-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            Utilice los filtros y haga clic en "Consultar" para ver los resultados
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div id="sin-resultados" class="hidden p-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-medium">No se encontraron resultados</p>
            <p class="text-sm mt-1">Intente ajustar los filtros de búsqueda</p>
        </div>
    </div>

@endsection

@push('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/consultas-descargas.js'])
    @else
        <!-- Fallback: JavaScript inline para desarrollo -->
        @php
            $jsPath = resource_path('js/consultas-descargas.js');
            $jsContent = file_exists($jsPath) ? file_get_contents($jsPath) : '';
        @endphp
        @if($jsContent)
            <script>
                {!! $jsContent !!}
            </script>
        @endif
    @endif
@endpush

