@extends('layouts.app')

@section('title', 'Bienvenida - Sistema de Asistencia')

@section('content')
    
    <!-- Mensajes de éxito -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    
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
            mauricio yaque
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
                
                <!-- Botón de ejemplo 1 -->
                <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow">
                    Registrar Asistencia
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
            
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-800">
                    <strong>Nota:</strong> Estos botones son solo ejemplos. 
                    Puedes agregar más funcionalidades según las necesidades de tu sistema.
                </p>
            </div>
        </div>
    </div>
    
@endsection
