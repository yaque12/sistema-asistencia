<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Asistencia')</title>
    
    <!-- Tailwind CSS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback: Tailwind CSS desde CDN (solo para desarrollo) -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Sidebar fijo a la izquierda -->
    <aside class="fixed left-0 top-0 h-full w-64 bg-blue-600 text-white shadow-lg z-30">
        <div class="flex flex-col h-full">
            <!-- Logo/Título del Sidebar -->
            <div class="p-6 border-b border-blue-500">
                <a href="{{ route('bienvenida') }}" class="block">
                    <h1 class="text-xl font-bold hover:text-blue-200 transition duration-200 cursor-pointer">Sistema de Asistencia</h1>
                </a>
            </div>
            
            <!-- Opciones de navegación -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <!-- Opción: Usuarios -->
                    <li>
                        <a href="{{ route('usuarios.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-blue-800' : 'bg-blue-700' }} hover:bg-blue-800 transition duration-200">
                            <span class="font-semibold">Usuarios</span>
                        </a>
                    </li>
                    
                    <!-- Opción: Empleados -->
                    <li>
                        <a href="{{ route('empleados.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('empleados.*') ? 'bg-blue-800' : 'bg-blue-700' }} hover:bg-blue-800 transition duration-200">
                            <span class="font-semibold">Empleados</span>
                        </a>
                    </li>
                    
                    <!-- Opción: Razones de ausentismos -->
                    <li>
                        <a href="{{ route('razones-ausentismos.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('razones-ausentismos.*') ? 'bg-blue-800' : 'bg-blue-700' }} hover:bg-blue-800 transition duration-200">
                            <span class="font-semibold">Razones de ausentismos</span>
                        </a>
                    </li>
                    
                    <!-- Opción: Reporte Diario -->
                    <li>
                        <a href="{{ route('reporte-diario.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('reporte-diario.*') ? 'bg-blue-800' : 'bg-blue-700' }} hover:bg-blue-800 transition duration-200">
                            <span class="font-semibold">Reporte Diario</span>
                        </a>
                    </li>
                    
                    <!-- Opción: Consultas y Descargas -->
                    <li>
                        <a href="{{ route('consultas-descargas.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('consultas-descargas.*') ? 'bg-blue-800' : 'bg-blue-700' }} hover:bg-blue-800 transition duration-200">
                            <span class="font-semibold">Consultas y Descargas</span>
                        </a>
                    </li>
                    
                    <!-- Opción: Generar Reporte -->
                    <li>
                        <a href="{{ route('generar-reporte.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('generar-reporte.*') ? 'bg-blue-800' : 'bg-blue-700' }} hover:bg-blue-800 transition duration-200">
                            <span class="font-semibold">Generar Reporte</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    
    <!-- Contenedor principal con margen izquierdo para el sidebar -->
    <div class="ml-64 min-h-screen flex flex-col">
        
        <!-- Barra de navegación superior -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Espaciador izquierdo -->
                    <div class="flex-1"></div>
                    
                    <!-- Logo centrado -->
                    <div class="flex-1 flex justify-center">
                        <img 
                            src="{{ asset('images/logo.jpg') }}" 
                            alt="Logo de la Empresa" 
                            class="h-12 w-auto object-contain"
                        >
                    </div>
                    
                    <!-- Botón de cerrar sesión -->
                    <div class="flex-1 flex justify-end">
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
        <main class="flex-1 px-6 py-8">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-800 text-white">
            <div class="px-6 py-4">
                <div class="text-center">
                    <p class="text-sm">
                        Sistema de Asistencia © {{ date('Y') }} - Todos los derechos reservados
                    </p>
                </div>
            </div>
        </footer>
        
    </div>
    
    @stack('scripts')
</body>
</html>

