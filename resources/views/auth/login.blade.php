<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Asistencia</title>
    
    <!-- Tailwind CSS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback: Tailwind CSS desde CDN (solo para desarrollo) -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    
    <!-- Contenedor principal del formulario -->
    <div class="w-full max-w-md">
        
        <!-- Tarjeta del formulario -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            
            <!-- Encabezado -->
            <div class="bg-blue-600 text-white p-6 text-center">
                <h1 class="text-2xl font-bold">Sistema de Asistencia</h1>
                <p class="text-blue-100 mt-2">Iniciar Sesión</p>
            </div>
            
            <!-- Cuerpo del formulario -->
            <div class="p-8">
                
                <!-- Mensajes de información (cuando se crea el usuario supervisor) -->
                @if(session('info'))
                    <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                        <p class="text-sm">{{ session('info') }}</p>
                    </div>
                @endif
                
                <!-- Mensajes de éxito -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                @endif
                
                <!-- Mensajes de error -->
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Formulario de login -->
                <form method="POST" action="/login">
                    
                    <!-- Token CSRF (obligatorio en Laravel para seguridad) -->
                    @csrf
                    
                    <!-- Campo: Nombre de Usuario -->
                    <div class="mb-4">
                        <label for="nombre_usuario" class="block text-gray-700 font-semibold mb-2">
                            Nombre de Usuario
                        </label>
                        <input 
                            type="text" 
                            id="nombre_usuario" 
                            name="nombre_usuario" 
                            value="{{ old('nombre_usuario') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ingrese su nombre de usuario"
                            required
                            autofocus
                        >
                    </div>
                    
                    <!-- Campo: Contraseña -->
                    <div class="mb-6">
                        <label for="clave" class="block text-gray-700 font-semibold mb-2">
                            Contraseña
                        </label>
                        <input 
                            type="password" 
                            id="clave" 
                            name="clave" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ingrese su contraseña"
                            required
                        >
                    </div>
                    
                    <!-- Botón de envío -->
                    <div class="mb-4">
                        <button 
                            type="submit" 
                            class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition duration-200"
                        >
                            Iniciar Sesión
                        </button>
                    </div>
                    
                </form>
                
                <!-- Información adicional -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 text-center">
                        <strong>Nota:</strong> contacte al admin para ingresar al sistema
                        <br>
                        <span class="font-mono text-blue-600">usuario</span> / 
                        <span class="font-mono text-blue-600">contraseña</span>
                    </p>
                </div>
                
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-gray-600 text-sm">
                Sistema de Asistencia © {{ date('Y') }}
            </p>
        </div>
        
    </div>
    
</body>
</html>

