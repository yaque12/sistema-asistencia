<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - Sistema de Asistencia</title>
    
    <!-- Tailwind CSS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback: Tailwind CSS desde CDN (solo para desarrollo) -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo/Título -->
                <div>
                    <h1 class="text-xl font-bold">Sistema de Asistencia</h1>
                </div>
                
                <!-- Botón de cerrar sesión -->
                <div>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button 
                            type="submit" 
                            class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded transition duration-200"
                        >
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Contenido principal -->
    <div class="container mx-auto px-6 py-8">
        
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
        
    </div>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4">
            <div class="text-center">
                <p class="text-sm">
                    Sistema de Asistencia © {{ date('Y') }} - Todos los derechos reservados
                </p>
            </div>
        </div>
    </footer>
    
</body>
</html>

