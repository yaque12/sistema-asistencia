@extends('layouts.app')

@section('title', 'Generar Reporte - Sistema de Asistencia')

@section('content')
    
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Generar Reporte</h1>
    </div>

    <!-- Botón Generar Reporte (inicial) -->
    <div id="contenedor-boton-inicial" class="flex justify-center items-center mb-6">
        <button 
            id="btn-generar-reporte"
            class="bg-green-500 hover:bg-green-600 text-white font-semibold px-8 py-4 rounded-lg transition duration-200 shadow-md text-lg"
        >
            Generar Reporte
        </button>
    </div>

    <!-- Tabla de reportes -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-reportes-body" class="bg-white divide-y divide-gray-200">
                    <!-- Los reportes se cargarán dinámicamente desde JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div id="sin-resultados" class="hidden p-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-medium">No se encontraron reportes</p>
            <p class="text-sm mt-1">Haz clic en "Generar Reporte" para crear uno nuevo</p>
        </div>
    </div>

    <!-- Contenedor para mensajes globales -->
    <div id="mensaje-global" class="hidden mb-6 p-4 rounded-lg"></div>

    <!-- Formulario (oculto inicialmente) -->
    <div id="contenedor-formulario" class="hidden">
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <form id="formulario-generar-reporte">
                <!-- Campo: Fecha -->
                <div class="mb-6">
                    <label for="fecha" class="block text-sm font-semibold text-gray-700 mb-2">
                        Fecha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha" 
                        name="fecha"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        required
                    >
                </div>

                <!-- Campo: Estado -->
                <div class="mb-6">
                    <label for="estado" class="block text-sm font-semibold text-gray-700 mb-2">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="estado" 
                        name="estado"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        required
                    >
                        <option value="">Seleccione el estado</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <!-- Campo: Comentarios -->
                <div class="mb-6">
                    <label for="comentarios" class="block text-sm font-semibold text-gray-700 mb-2">
                        Comentarios
                    </label>
                    <textarea 
                        id="comentarios" 
                        name="comentarios"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ingrese sus comentarios aquí..."
                    ></textarea>
                </div>

                <!-- Botón Guardar -->
                <div class="flex justify-end">
                    <button 
                        type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 shadow-md flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Obtener token CSRF del meta tag
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        }

        // Mostrar mensaje de éxito o error
        function mostrarMensaje(tipo, mensaje) {
            // Crear contenedor de mensaje si no existe
            let contenedorMensaje = document.getElementById('mensaje-global');
            
            if (!contenedorMensaje) {
                contenedorMensaje = document.createElement('div');
                contenedorMensaje.id = 'mensaje-global';
                contenedorMensaje.className = 'mb-6 p-4 rounded-lg';
                
                const contenedorFormulario = document.getElementById('contenedor-formulario');
                contenedorFormulario.insertBefore(contenedorMensaje, contenedorFormulario.firstChild);
            }

            // Configurar estilos según el tipo
            if (tipo === 'success') {
                contenedorMensaje.className = 'mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700';
            } else {
                contenedorMensaje.className = 'mb-6 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700';
            }

            contenedorMensaje.textContent = mensaje;
            contenedorMensaje.classList.remove('hidden');

            // Ocultar mensaje después de 5 segundos
            setTimeout(() => {
                contenedorMensaje.classList.add('hidden');
            }, 5000);
        }

        // Formatear fecha para mostrar
        function formatearFecha(fecha) {
            if (!fecha) return '-';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
        }

        // Cargar reportes desde el servidor
        async function cargarReportes() {
            try {
                const response = await fetch('{{ route("generar-reporte.index") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                });

                const resultado = await response.json();

                if (resultado.success) {
                    mostrarTablaReportes(resultado.data.reportes);
                } else {
                    mostrarMensaje('error', 'Error al cargar los reportes.');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('error', 'Error al cargar los reportes.');
            }
        }

        // Mostrar tabla de reportes
        function mostrarTablaReportes(reportes) {
            const tbody = document.getElementById('tabla-reportes-body');
            const sinResultados = document.getElementById('sin-resultados');

            if (!reportes || reportes.length === 0) {
                tbody.innerHTML = '';
                sinResultados.classList.remove('hidden');
                return;
            }

            sinResultados.classList.add('hidden');
            
            tbody.innerHTML = reportes.map(reporte => {
                const estadoClass = reporte.estado === 'activo' 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800';
                const nuevoEstado = reporte.estado === 'activo' ? 'inactivo' : 'activo';
                const textoNuevoEstado = reporte.estado === 'activo' ? 'Inactivo' : 'Activo';

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${formatearFecha(reporte.fecha)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${estadoClass}">
                                ${reporte.estado.charAt(0).toUpperCase() + reporte.estado.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            ${reporte.comentarios || '-'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button 
                                class="btn-editar-reporte text-blue-600 hover:text-blue-900 mr-4"
                                data-reporte-id="${reporte.id_reporte}"
                                data-nuevo-estado="${nuevoEstado}"
                            >
                                Cambiar a ${textoNuevoEstado}
                            </button>
                            <button 
                                class="btn-eliminar-reporte text-red-600 hover:text-red-900"
                                data-reporte-id="${reporte.id_reporte}"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Editar reporte (cambiar estado)
        async function editarReporte(reporteId, nuevoEstado) {
            if (!confirm('¿Está seguro de que desea cambiar el estado del reporte?')) {
                return;
            }

            try {
                const response = await fetch(`/generar-reporte/${reporteId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ estado: nuevoEstado }),
                });

                const resultado = await response.json();

                if (resultado.success) {
                    mostrarMensaje('success', resultado.message || 'Reporte actualizado exitosamente.');
                    cargarReportes();
                } else {
                    mostrarMensaje('error', resultado.message || 'Error al actualizar el reporte.');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('error', 'Ocurrió un error al actualizar el reporte.');
            }
        }

        // Eliminar reporte
        async function eliminarReporte(reporteId) {
            if (!confirm('¿Está seguro de que desea eliminar este reporte?')) {
                return;
            }

            try {
                const response = await fetch(`/generar-reporte/${reporteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                });

                const resultado = await response.json();

                if (resultado.success) {
                    mostrarMensaje('success', resultado.message || 'Reporte eliminado exitosamente.');
                    cargarReportes();
                } else {
                    mostrarMensaje('error', resultado.message || 'Error al eliminar el reporte.');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('error', 'Ocurrió un error al eliminar el reporte.');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btnGenerarReporte = document.getElementById('btn-generar-reporte');
            const contenedorBotonInicial = document.getElementById('contenedor-boton-inicial');
            const contenedorFormulario = document.getElementById('contenedor-formulario');
            const formulario = document.getElementById('formulario-generar-reporte');
            const btnGuardar = formulario.querySelector('button[type="submit"]');

            // Cargar reportes al iniciar
            cargarReportes();

            // Delegación de eventos para botones editar y eliminar
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-editar-reporte')) {
                    e.preventDefault();
                    const reporteId = parseInt(e.target.getAttribute('data-reporte-id'));
                    const nuevoEstado = e.target.getAttribute('data-nuevo-estado');
                    editarReporte(reporteId, nuevoEstado);
                }
                if (e.target.classList.contains('btn-eliminar-reporte')) {
                    e.preventDefault();
                    const reporteId = parseInt(e.target.getAttribute('data-reporte-id'));
                    eliminarReporte(reporteId);
                }
            });

            // Mostrar formulario al hacer clic en el botón "Generar Reporte"
            btnGenerarReporte.addEventListener('click', function() {
                contenedorBotonInicial.classList.add('hidden');
                contenedorFormulario.classList.remove('hidden');
            });

            // Manejar envío del formulario
            formulario.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Obtener datos del formulario
                const formData = new FormData(formulario);
                const datos = {
                    fecha: formData.get('fecha'),
                    estado: formData.get('estado'),
                    comentarios: formData.get('comentarios') || null,
                };

                // Validación básica del lado del cliente
                if (!datos.fecha) {
                    mostrarMensaje('error', 'Por favor, seleccione una fecha.');
                    return;
                }

                if (!datos.estado) {
                    mostrarMensaje('error', 'Por favor, seleccione un estado.');
                    return;
                }

                // Deshabilitar botón durante el envío
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = `
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando...
                `;

                try {
                    const response = await fetch('{{ route("generar-reporte.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(datos),
                    });

                    const resultado = await response.json();

                    if (resultado.success) {
                        mostrarMensaje('success', resultado.message || 'Reporte guardado exitosamente.');
                        
                        // Limpiar formulario
                        formulario.reset();
                        
                        // Recargar tabla de reportes
                        cargarReportes();
                        
                        // Ocultar formulario y mostrar botón inicial después de 2 segundos
                        setTimeout(() => {
                            contenedorFormulario.classList.add('hidden');
                            contenedorBotonInicial.classList.remove('hidden');
                        }, 2000);
                    } else {
                        // Mostrar errores de validación
                        let mensajeError = resultado.message || 'Error al guardar el reporte.';
                        
                        if (resultado.errors) {
                            const errores = Object.values(resultado.errors).flat();
                            mensajeError = errores.join(' ');
                        }
                        
                        mostrarMensaje('error', mensajeError);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    mostrarMensaje('error', 'Ocurrió un error al guardar el reporte. Por favor, intenta nuevamente.');
                } finally {
                    // Restaurar botón
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = `
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar
                    `;
                }
            });
        });
    </script>
@endpush

