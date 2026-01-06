@extends('layouts.app')

@section('title', 'Bienvenida - Sistema de Asistencia')

@section('content')
    
    <!-- Tarjeta de bienvenida -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-8">
            <h2 class="text-3xl font-bold mb-2">
                ¡Bienvenido, {{ $usuario->nombres }} {{ $usuario->apellidos }}!
            </h2>
            <p class="text-blue-100">
                Has iniciado sesión correctamente en el Sistema de Asistencia
            </p>
        </div>
    </div>
    
    <!-- Nombre de la empresa -->
    <div class="text-center mb-6">
        <h2 class="text-5xl font-extrabold text-green-800">
            DECHOTO.COM
        </h2>
    </div>
    
    <!-- Información del usuario -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Información del Usuario</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Nombre de Usuario -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">
                        Nombre de Usuario
                    </label>
                    <p class="text-gray-800 text-lg">
                        {{ $usuario->nombre_usuario }}
                    </p>
                </div>
                
                <!-- Nombres -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">
                        Nombres
                    </label>
                    <p class="text-gray-800 text-lg">
                        {{ $usuario->nombres }}
                    </p>
                </div>
                
                <!-- Apellidos -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">
                        Apellidos
                    </label>
                    <p class="text-gray-800 text-lg">
                        {{ $usuario->apellidos }}
                    </p>
                </div>
                
                <!-- Departamento de Trabajo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">
                        Departamento de Trabajo
                    </label>
                    <p class="text-gray-800 text-lg">
                        {{ $usuario->departamento_trabajo ?? 'No especificado' }}
                    </p>
                </div>
                
                <!-- Código de Empleado -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">
                        Código de Empleado
                    </label>
                    <p class="text-gray-800 text-lg">
                        {{ $usuario->codigo_empleado ?? 'No especificado' }}
                    </p>
                </div>
                
                <!-- Fecha de Registro -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">
                        Fecha de Registro
                    </label>
                    <p class="text-gray-800 text-lg">
                        {{ $usuario->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Tarjeta de acciones rápidas -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Acciones Rápidas</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- Botón de Panel de Control de Asistencia con indicador -->
                <button 
                    id="btnRegistrarAsistencia" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow relative"
                >
                    <span>Panel de Control de Asistencia</span>
                    <span id="badgeAsistencia" class="absolute -top-2 -right-2 bg-green-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center hidden">
                        <span id="porcentajeBadge">0%</span>
                    </span>
                </button>
                
                <!-- Botón de ejemplo 2 -->
                <button class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow">
                    Ver Reportes
                </button>
                
                <!-- Botón de ejemplo 3 -->
                <button class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow">
                    Configuración
                </button>
                
            </div>
        </div>
    </div>
    
    <!-- Modal de Panel de Control de Asistencia -->
    <div 
        id="modalPanelAsistencia" 
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header del Modal -->
            <div class="bg-blue-600 dark:bg-blue-800 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h3 class="text-2xl font-bold">Panel de Control de Asistencia</h3>
                <button 
                    id="btnCerrarModal" 
                    class="text-white hover:text-gray-200 text-2xl font-bold"
                >
                    ×
                </button>
            </div>
            
            <!-- Contenido del Modal -->
            <div class="p-6">
                <!-- Indicador del Día Actual -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-700 dark:to-blue-800 rounded-lg p-6 mb-6 text-white">
                    <h4 class="text-lg font-semibold mb-2">Asistencia del Día</h4>
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <p class="text-sm opacity-90 mb-1" id="fechaActualTexto">Cargando...</p>
                            <p class="text-4xl font-bold" id="porcentajeDiaActual">0%</p>
                            <p class="text-sm opacity-90 mt-2" id="detalleDiaActual">
                                <span id="personasConHoras">0</span> / <span id="totalEmpleados">0</span> empleados
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold" id="iconoEstado">📊</div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfica Semanal -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <h4 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        Asistencia Semanal
                    </h4>
                    <div class="relative h-64">
                        <canvas id="graficaSemanal"></canvas>
                    </div>
                </div>
                
                <!-- Botones de Acción -->
                <div class="mt-6 flex justify-end gap-3">
                    <a 
                        href="{{ route('reporte-diario.index') }}" 
                        class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
                    >
                        Ir a Registrar Asistencia
                    </a>
                    <button 
                        id="btnCerrarModal2" 
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@push('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/panel-asistencia.js'])
    @else
        <!-- Fallback: JavaScript inline para desarrollo -->
        @php
            $jsPath = resource_path('js/panel-asistencia.js');
            $jsContent = file_exists($jsPath) ? file_get_contents($jsPath) : '';
        @endphp
        @if($jsContent)
            <script>
                {!! $jsContent !!}
            </script>
        @endif
    @endif
@endpush
