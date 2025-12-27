@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Sistema de Asistencia')

@push('styles')
    <style>
        /* Estilos adicionales para modales si es necesario */
    </style>
@endpush

@section('content')
    
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <p class="text-gray-600 mt-2">Administra los usuarios del sistema</p>
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
                        id="buscar-usuario" 
                        placeholder="Buscar por nombre, apellido, usuario o departamento..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Botón Nuevo Usuario -->
            <button 
                id="btn-nuevo-usuario"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg transition duration-200 shadow-md flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre de Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombres</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellidos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código Empleado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-usuarios-body" class="bg-white divide-y divide-gray-200">
                    @foreach($usuarios as $usuario)
                    <tr class="usuario-fila hover:bg-gray-50" data-usuario-id="{{ $usuario->id_usuario }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->id_usuario }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->nombre_usuario }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->nombres }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->apellidos }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->departamento_trabajo ?? 'No especificado' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->codigo_empleado ?? 'No especificado' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button 
                                class="btn-editar text-blue-600 hover:text-blue-900 mr-3" 
                                data-usuario-id="{{ $usuario->id_usuario }}"
                                data-nombre-usuario="{{ $usuario->nombre_usuario }}"
                                data-nombres="{{ $usuario->nombres }}"
                                data-apellidos="{{ $usuario->apellidos }}"
                                data-departamento="{{ $usuario->departamento_trabajo ?? '' }}"
                                data-codigo-empleado="{{ $usuario->codigo_empleado ?? '' }}"
                            >
                                Editar
                            </button>
                            <button 
                                class="btn-eliminar text-red-600 hover:text-red-900" 
                                data-usuario-id="{{ $usuario->id_usuario }}"
                                data-nombre-usuario="{{ $usuario->nombre_usuario }}"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div id="sin-resultados" class="hidden p-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-medium">No se encontraron usuarios</p>
            <p class="text-sm mt-1">Intenta con otros términos de búsqueda</p>
        </div>

        <!-- Paginación -->
        <div id="paginacion-usuarios" class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando <span id="mostrando-desde">{{ $usuarios->firstItem() ?? 0 }}</span> a <span id="mostrando-hasta">{{ $usuarios->lastItem() ?? 0 }}</span> de <span id="total-usuarios">{{ $usuarios->total() }}</span> usuarios
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
    @include('usuarios.partials.modal-crear')
    @include('usuarios.partials.modal-editar')

    <!-- Datos iniciales para JavaScript (opcional, ya que se cargan desde el servidor) -->

@endsection

@push('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/usuarios.js'])
    @else
        <!-- Fallback: JavaScript inline para desarrollo -->
        @php
            $jsPath = resource_path('js/usuarios.js');
            $jsContent = file_exists($jsPath) ? file_get_contents($jsPath) : '';
        @endphp
        @if($jsContent)
            <script>
                {!! $jsContent !!}
            </script>
        @endif
    @endif
@endpush

