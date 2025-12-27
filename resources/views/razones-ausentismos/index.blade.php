@extends('layouts.app')

@section('title', 'Gestión de Razones de Ausentismos - Sistema de Asistencia')

@push('styles')
    <style>
        /* Estilos adicionales para modales si es necesario */
    </style>
@endpush

@section('content')
    
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestión de Razones de Ausentismos</h1>
        <p class="text-gray-600 mt-2">Administra las razones de ausentismos del sistema</p>
    </div>

    <!-- Contenedor para mensajes globales -->
    <div id="mensaje-global" class="hidden mb-6 p-4 rounded-lg"></div>

    <!-- Barra de búsqueda y acciones -->
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Barra de búsqueda -->
            <div class="flex-1 w-full md:w-auto">
                <div class="relative">
                    <input 
                        type="text" 
                        id="buscar-razon" 
                        placeholder="Buscar por razón, código o descripción..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Botón Nueva Razón de Ausentismo -->
            <button 
                id="btn-nueva-razon"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg transition duration-200 shadow-md flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Razón de Ausentismo
            </button>
        </div>
    </div>

    <!-- Tabla de razones de ausentismos -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razón</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código de Razón de Ausentismo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-razones-body" class="bg-white divide-y divide-gray-200">
                    <!-- Las razones se cargarán dinámicamente desde JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div id="sin-resultados" class="hidden p-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-medium">No se encontraron razones de ausentismos</p>
            <p class="text-sm mt-1">Intenta con otros términos de búsqueda</p>
        </div>

        <!-- Paginación -->
        <div id="paginacion-razones" class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando <span id="mostrando-desde">0</span> a <span id="mostrando-hasta">0</span> de <span id="total-razones">0</span> razones de ausentismos
            </div>
            <div class="flex gap-2">
                <button id="btn-pagina-anterior" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Anterior
                </button>
                <div id="numeros-paginas" class="flex gap-2">
                    <!-- Los números de página se generan dinámicamente -->
                </div>
                <button id="btn-pagina-siguiente" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- Incluir modales -->
    @include('razones-ausentismos.partials.modal-crear')
    @include('razones-ausentismos.partials.modal-editar')

@endsection

@push('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/razones-ausentismos.js'])
    @else
        <!-- Fallback: JavaScript inline para desarrollo -->
        @php
            $jsPath = resource_path('js/razones-ausentismos.js');
            $jsContent = file_exists($jsPath) ? file_get_contents($jsPath) : '';
        @endphp
        @if($jsContent)
            <script>
                {!! $jsContent !!}
            </script>
        @endif
    @endif
@endpush

