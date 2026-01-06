<!DOCTYPE html>
<html lang="es" class="">
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
        <script>
            tailwind.config = {
                darkMode: 'class',
            }
        </script>
    @endif
    
    <!-- Chart.js para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Script para inicializar el tema antes de que se cargue la página -->
    <script>
        (function() {
            const temaGuardado = localStorage.getItem('tema');
            const temaPreferido = temaGuardado || 'light';
            if (temaPreferido === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-200">
    
    <!-- Sidebar fijo a la izquierda -->
    <aside class="fixed left-0 top-0 h-full w-64 bg-blue-600 dark:bg-blue-800 text-white shadow-lg z-30 transition-colors duration-200">
        <div class="flex flex-col h-full">
            <!-- Logo/Título del Sidebar -->
            <div class="p-6 border-b border-blue-500 dark:border-blue-700">
                <a href="{{ route('bienvenida') }}" class="block">
                    <h1 class="text-xl font-bold hover:text-blue-200 dark:hover:text-blue-300 transition duration-200 cursor-pointer">Sistema de Asistencia</h1>
                </a>
            </div>
            
            <!-- Opciones de navegación -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    @php
                        $user = auth()->user();
                        if ($user) {
                            try {
                                $user->load('roles');
                                $rolesUsuario = $user->roles->pluck('codigo_rol')->toArray();
                                $esAdminOSupervisor = $user->esAdminOSupervisor();
                                $tieneGerenciacontable = in_array('gerenciacontable01', $rolesUsuario);
                                $tieneRRHH = in_array('RRHH.PLAN', $rolesUsuario);
                            } catch (\Exception $e) {
                                // Si las tablas de roles no existen aún, mostrar todos los módulos
                                // Esto permite que el sistema funcione mientras se ejecuta el SQL
                                $rolesUsuario = [];
                                $esAdminOSupervisor = true; // Permitir acceso completo temporalmente
                                $tieneGerenciacontable = true;
                                $tieneRRHH = true;
                            }
                        } else {
                            $rolesUsuario = [];
                            $esAdminOSupervisor = false;
                            $tieneGerenciacontable = false;
                            $tieneRRHH = false;
                        }
                    @endphp
                    
                    <!-- Opción: Usuarios - Solo ADMIN y supervisor -->
                    @if($esAdminOSupervisor)
                    <li>
                        <a href="{{ route('usuarios.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-blue-800 dark:bg-blue-900' : 'bg-blue-700 dark:bg-blue-800' }} hover:bg-blue-800 dark:hover:bg-blue-900 transition duration-200">
                            <span class="font-semibold">Usuarios</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Opción: Empleados - ADMIN, supervisor, gerenciacontable01 -->
                    @if($esAdminOSupervisor || $tieneGerenciacontable)
                    <li>
                        <a href="{{ route('empleados.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('empleados.*') ? 'bg-blue-800 dark:bg-blue-900' : 'bg-blue-700 dark:bg-blue-800' }} hover:bg-blue-800 dark:hover:bg-blue-900 transition duration-200">
                            <span class="font-semibold">Empleados</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Opción: Razones de ausentismos - ADMIN, supervisor, gerenciacontable01 -->
                    @if($esAdminOSupervisor || $tieneGerenciacontable)
                    <li>
                        <a href="{{ route('razones-ausentismos.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('razones-ausentismos.*') ? 'bg-blue-800 dark:bg-blue-900' : 'bg-blue-700 dark:bg-blue-800' }} hover:bg-blue-800 dark:hover:bg-blue-900 transition duration-200">
                            <span class="font-semibold">Razones de ausentismos</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Opción: Reporte Diario - ADMIN, supervisor, RRHH.PLAN -->
                    @if($esAdminOSupervisor || $tieneRRHH)
                    <li>
                        <a href="{{ route('reporte-diario.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('reporte-diario.*') ? 'bg-blue-800 dark:bg-blue-900' : 'bg-blue-700 dark:bg-blue-800' }} hover:bg-blue-800 dark:hover:bg-blue-900 transition duration-200">
                            <span class="font-semibold">Reporte Diario</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Opción: Consultas y Descargas - ADMIN, supervisor, gerenciacontable01, RRHH.PLAN -->
                    @if($esAdminOSupervisor || $tieneGerenciacontable || $tieneRRHH)
                    <li>
                        <a href="{{ route('consultas-descargas.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('consultas-descargas.*') ? 'bg-blue-800 dark:bg-blue-900' : 'bg-blue-700 dark:bg-blue-800' }} hover:bg-blue-800 dark:hover:bg-blue-900 transition duration-200">
                            <span class="font-semibold">Consultas y Descargas</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Opción: Generar Reporte - Solo ADMIN y supervisor -->
                    @if($esAdminOSupervisor)
                    <li>
                        <a href="{{ route('generar-reporte.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('generar-reporte.*') ? 'bg-blue-800 dark:bg-blue-900' : 'bg-blue-700 dark:bg-blue-800' }} hover:bg-blue-800 dark:hover:bg-blue-900 transition duration-200">
                            <span class="font-semibold">Generar Reporte</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>
    
    <!-- Contenedor principal con margen izquierdo para el sidebar -->
    <div class="ml-64 min-h-screen flex flex-col">
        
        <!-- Barra de navegación superior -->
        <nav class="bg-blue-600 dark:bg-blue-800 text-white shadow-lg transition-colors duration-200">
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
                    
                    <!-- Selector de tema y botón de cerrar sesión -->
                    <div class="flex-1 flex justify-end items-center gap-3">
                        <!-- Selector de tema -->
                        <button 
                            id="toggleTema"
                            type="button"
                            class="p-2 rounded-lg bg-blue-700 dark:bg-blue-900 hover:bg-blue-800 dark:hover:bg-blue-700 text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-blue-800"
                            aria-label="Alternar tema"
                            title="Alternar entre modo claro y oscuro"
                        >
                            <!-- Ícono de sol (modo claro) -->
                            <span id="iconoSol" class="text-xl">☀️</span>
                            <!-- Ícono de luna (modo oscuro) -->
                            <span id="iconoLuna" class="text-xl hidden">🌙</span>
                        </button>
                        
                        <!-- Botón de cerrar sesión -->
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white font-semibold px-4 py-2 rounded transition duration-200"
                            >
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Contenido principal -->
        <main class="flex-1 px-6 py-8 bg-white dark:bg-gray-800 transition-colors duration-200">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-800 dark:bg-gray-900 text-white transition-colors duration-200">
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
    
    <!-- Script para manejar el cambio de tema -->
    <script>
        (function() {
            // Esperar a que el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', inicializar);
            } else {
                inicializar();
            }
            
            function inicializar() {
                const toggleTema = document.getElementById('toggleTema');
                const iconoSol = document.getElementById('iconoSol');
                const iconoLuna = document.getElementById('iconoLuna');
                const html = document.documentElement;
                
                // Función para actualizar el tema
                function actualizarTema(esOscuro) {
                    if (esOscuro) {
                        html.classList.add('dark');
                        iconoSol.classList.add('hidden');
                        iconoLuna.classList.remove('hidden');
                        localStorage.setItem('tema', 'dark');
                    } else {
                        html.classList.remove('dark');
                        iconoSol.classList.remove('hidden');
                        iconoLuna.classList.add('hidden');
                        localStorage.setItem('tema', 'light');
                    }
                }
                
                // Sincronizar los íconos con el tema actual
                function sincronizarIconos() {
                    const esOscuro = html.classList.contains('dark');
                    if (esOscuro) {
                        iconoSol.classList.add('hidden');
                        iconoLuna.classList.remove('hidden');
                    } else {
                        iconoSol.classList.remove('hidden');
                        iconoLuna.classList.add('hidden');
                    }
                }
                
                // Event listener para el botón
                toggleTema.addEventListener('click', function() {
                    const esOscuro = html.classList.contains('dark');
                    actualizarTema(!esOscuro);
                });
                
                // Sincronizar íconos al cargar
                sincronizarIconos();
            }
        })();
    </script>
</body>
</html>

