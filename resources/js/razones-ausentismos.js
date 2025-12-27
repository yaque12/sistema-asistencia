// Gestión de Razones de Ausentismos - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de razones de ausentismos en el frontend

// Variables globales
let paginaActual = 1;
let terminoBusqueda = '';
let timeoutBusqueda = null;
const razonesPorPagina = 15;

// Obtener token CSRF del meta tag
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

// Configurar fetch para incluir CSRF y headers necesarios
function fetchConfig(method, body = null) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
    };
    
    if (body) {
        config.body = JSON.stringify(body);
    }
    
    return config;
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarDatos();
    inicializarEventListeners();
});

/**
 * Carga los datos iniciales desde el servidor
 */
function inicializarDatos() {
    cargarRazones();
}

/**
 * Inicializa todos los event listeners
 */
function inicializarEventListeners() {
    // Botón nueva razón
    const btnNuevaRazon = document.getElementById('btn-nueva-razon');
    if (btnNuevaRazon) {
        btnNuevaRazon.addEventListener('click', abrirModalCrear);
    }

    // Botones cerrar modales
    const cerrarModalCrear = document.getElementById('cerrar-modal-crear');
    const cerrarModalEditar = document.getElementById('cerrar-modal-editar');
    const cancelarCrear = document.getElementById('cancelar-crear-razon');
    const cancelarEditar = document.getElementById('cancelar-editar-razon');

    if (cerrarModalCrear) {
        cerrarModalCrear.addEventListener('click', cerrarModalCrearRazon);
    }
    if (cerrarModalEditar) {
        cerrarModalEditar.addEventListener('click', cerrarModalEditarRazon);
    }
    if (cancelarCrear) {
        cancelarCrear.addEventListener('click', cerrarModalCrearRazon);
    }
    if (cancelarEditar) {
        cancelarEditar.addEventListener('click', cerrarModalEditarRazon);
    }

    // Cerrar modal al hacer clic fuera
    const modalCrear = document.getElementById('modal-crear-razon');
    const modalEditar = document.getElementById('modal-editar-razon');
    
    if (modalCrear) {
        modalCrear.addEventListener('click', function(e) {
            if (e.target === modalCrear) {
                cerrarModalCrearRazon();
            }
        });
    }
    
    if (modalEditar) {
        modalEditar.addEventListener('click', function(e) {
            if (e.target === modalEditar) {
                cerrarModalEditarRazon();
            }
        });
    }

    // Formularios
    const formCrear = document.getElementById('form-crear-razon');
    const formEditar = document.getElementById('form-editar-razon');

    if (formCrear) {
        formCrear.addEventListener('submit', manejarCrearRazon);
    }
    if (formEditar) {
        formEditar.addEventListener('submit', manejarEditarRazon);
    }

    // Búsqueda con debounce
    const inputBuscar = document.getElementById('buscar-razon');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', function(e) {
            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(() => {
                manejarBusqueda(e.target.value);
            }, 500);
        });
    }

    // Botones editar y eliminar (se agregan dinámicamente)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-editar')) {
            e.preventDefault();
            const razonId = parseInt(e.target.getAttribute('data-razon-id'));
            abrirModalEditar(razonId, e.target);
        }
        if (e.target.classList.contains('btn-eliminar')) {
            e.preventDefault();
            const razonId = parseInt(e.target.getAttribute('data-razon-id'));
            const razon = e.target.getAttribute('data-razon');
            confirmarEliminar(razonId, razon);
        }
    });
}

/**
 * Carga las razones desde el servidor
 */
async function cargarRazones() {
    try {
        mostrarLoading(true);
        
        const url = new URL('/razones-ausentismos', window.location.origin);
        url.searchParams.append('buscar', terminoBusqueda);
        url.searchParams.append('pagina', paginaActual);
        url.searchParams.append('por_pagina', razonesPorPagina);

        const response = await fetch(url.toString(), {
            ...fetchConfig('GET'),
            body: null,
        });

        const data = await response.json();

        if (data.success) {
            actualizarTabla(data.data.razones);
            actualizarPaginacion(data.data.paginacion);
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al cargar razones:', error);
        mostrarMensajeGlobal('error', 'Error al cargar las razones de ausentismos. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Abre el modal para crear una nueva razón de ausentismo
 */
function abrirModalCrear() {
    const modal = document.getElementById('modal-crear-razon');
    if (modal) {
        limpiarFormularioCrear();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra el modal de crear razón
 */
function cerrarModalCrearRazon() {
    const modal = document.getElementById('modal-crear-razon');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        limpiarFormularioCrear();
    }
}

/**
 * Limpia el formulario de crear razón
 */
function limpiarFormularioCrear() {
    const form = document.getElementById('form-crear-razon');
    if (form) {
        form.reset();
        limpiarErroresCrear();
        ocultarMensaje('mensaje-crear');
    }
}

/**
 * Limpia los mensajes de error del formulario crear
 */
function limpiarErroresCrear() {
    const errores = document.querySelectorAll('[id^="error-crear-"]');
    errores.forEach(error => {
        error.classList.add('hidden');
    });
}

/**
 * Maneja el envío del formulario de crear razón
 */
async function manejarCrearRazon(e) {
    e.preventDefault();
    
    limpiarErroresCrear();
    
    const formData = {
        razon: document.getElementById('crear-razon').value.trim(),
        codigo_razon_ausentismo: document.getElementById('crear-codigo-razon').value.trim(),
        descripcion: document.getElementById('crear-descripcion').value.trim() || null
    };

    // Validación básica del frontend
    if (!formData.razon || !formData.codigo_razon_ausentismo) {
        mostrarMensajeGlobal('error', 'Por favor, complete todos los campos requeridos.');
        if (!formData.razon) {
            mostrarError('error-crear-razon', 'Este campo es requerido');
        }
        if (!formData.codigo_razon_ausentismo) {
            mostrarError('error-crear-codigo-razon', 'Este campo es requerido');
        }
        return;
    }

    try {
        mostrarLoadingFormulario('form-crear-razon', true);

        const response = await fetch('/razones-ausentismos', fetchConfig('POST', formData));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Razón de ausentismo creada exitosamente.');
            cerrarModalCrearRazon();
            paginaActual = 1; // Volver a la primera página
            cargarRazones(); // Recargar la lista
        } else {
            manejarErroresValidacion(data.errors, 'crear-');
            mostrarMensaje('error', data.message || 'Error al crear la razón de ausentismo.', 'mensaje-crear');
        }
    } catch (error) {
        console.error('Error al crear razón:', error);
        mostrarMensajeGlobal('error', 'Error al crear la razón de ausentismo. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingFormulario('form-crear-razon', false);
    }
}

/**
 * Abre el modal para editar una razón de ausentismo
 */
function abrirModalEditar(razonId, boton) {
    // Obtener datos de la razón desde los atributos del botón
    const razon = boton.getAttribute('data-razon');
    const codigoRazon = boton.getAttribute('data-codigo-razon');
    const descripcion = boton.getAttribute('data-descripcion') || '';

    // Llenar el formulario
    document.getElementById('editar-id-razon').value = razonId;
    document.getElementById('editar-razon').value = razon || '';
    document.getElementById('editar-codigo-razon').value = codigoRazon || '';
    document.getElementById('editar-descripcion').value = descripcion;

    // Ocultar mensajes de error
    limpiarErroresEditar();

    const modal = document.getElementById('modal-editar-razon');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra el modal de editar razón
 */
function cerrarModalEditarRazon() {
    const modal = document.getElementById('modal-editar-razon');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        limpiarFormularioEditar();
    }
}

/**
 * Limpia el formulario de editar razón
 */
function limpiarFormularioEditar() {
    const form = document.getElementById('form-editar-razon');
    if (form) {
        form.reset();
        limpiarErroresEditar();
        ocultarMensaje('mensaje-editar');
    }
}

/**
 * Limpia los mensajes de error del formulario editar
 */
function limpiarErroresEditar() {
    const errores = document.querySelectorAll('[id^="error-editar-"]');
    errores.forEach(error => {
        error.classList.add('hidden');
    });
}

/**
 * Maneja el envío del formulario de editar razón
 */
async function manejarEditarRazon(e) {
    e.preventDefault();
    
    limpiarErroresEditar();
    
    const idRazon = parseInt(document.getElementById('editar-id-razon').value);
    const formData = {
        razon: document.getElementById('editar-razon').value.trim(),
        codigo_razon_ausentismo: document.getElementById('editar-codigo-razon').value.trim(),
        descripcion: document.getElementById('editar-descripcion').value.trim() || null
    };

    // Validación básica del frontend
    if (!formData.razon || !formData.codigo_razon_ausentismo) {
        mostrarMensajeGlobal('error', 'Por favor, complete todos los campos requeridos.');
        if (!formData.razon) {
            mostrarError('error-editar-razon', 'Este campo es requerido');
        }
        if (!formData.codigo_razon_ausentismo) {
            mostrarError('error-editar-codigo-razon', 'Este campo es requerido');
        }
        return;
    }

    try {
        mostrarLoadingFormulario('form-editar-razon', true);

        const response = await fetch(`/razones-ausentismos/${idRazon}`, fetchConfig('PUT', formData));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Razón de ausentismo actualizada exitosamente.');
            cerrarModalEditarRazon();
            cargarRazones(); // Recargar la lista
        } else {
            manejarErroresValidacion(data.errors, 'editar-');
            mostrarMensaje('error', data.message || 'Error al actualizar la razón de ausentismo.', 'mensaje-editar');
        }
    } catch (error) {
        console.error('Error al actualizar razón:', error);
        mostrarMensajeGlobal('error', 'Error al actualizar la razón de ausentismo. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingFormulario('form-editar-razon', false);
    }
}

/**
 * Confirma y elimina una razón de ausentismo
 */
async function confirmarEliminar(razonId, razon) {
    if (!confirm(`¿Está seguro de que desea eliminar la razón de ausentismo "${razon}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }

    try {
        mostrarLoading(true);

        const response = await fetch(`/razones-ausentismos/${razonId}`, fetchConfig('DELETE'));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Razón de ausentismo eliminada exitosamente.');
            cargarRazones(); // Recargar la lista
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al eliminar razón:', error);
        mostrarMensajeGlobal('error', 'Error al eliminar la razón de ausentismo. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Maneja la búsqueda de razones
 */
function manejarBusqueda(termino) {
    terminoBusqueda = termino.trim();
    paginaActual = 1;
    cargarRazones();
}

/**
 * Actualiza la tabla de razones con los datos recibidos
 */
function actualizarTabla(razonesPagina) {
    const tbody = document.getElementById('tabla-razones-body');
    const sinResultados = document.getElementById('sin-resultados');
    
    if (!tbody) return;

    if (!razonesPagina || razonesPagina.length === 0) {
        tbody.innerHTML = '';
        if (sinResultados) {
            sinResultados.classList.remove('hidden');
        }
        return;
    }

    if (sinResultados) {
        sinResultados.classList.add('hidden');
    }

    // Generar filas de la tabla
    tbody.innerHTML = razonesPagina.map(razon => {
        return `
            <tr class="razon-fila hover:bg-gray-50" data-razon-id="${razon.id_razon}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${razon.razon || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${razon.codigo_razon_ausentismo || ''}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${razon.descripcion || 'No especificada'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                        class="btn-editar text-blue-600 hover:text-blue-900 mr-3" 
                        data-razon-id="${razon.id_razon}"
                        data-razon="${razon.razon || ''}"
                        data-codigo-razon="${razon.codigo_razon_ausentismo || ''}"
                        data-descripcion="${razon.descripcion || ''}"
                    >
                        Editar
                    </button>
                    <button 
                        class="btn-eliminar text-red-600 hover:text-red-900" 
                        data-razon-id="${razon.id_razon}"
                        data-razon="${razon.razon || ''}"
                    >
                        Eliminar
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Actualiza la información y controles de paginación
 */
function actualizarPaginacion(paginacion) {
    if (!paginacion) return;

    const { pagina_actual, ultima_pagina, total, desde, hasta } = paginacion;
    paginaActual = pagina_actual;

    // Actualizar texto de información
    const mostrandoDesde = document.getElementById('mostrando-desde');
    const mostrandoHasta = document.getElementById('mostrando-hasta');
    const totalRazonesSpan = document.getElementById('total-razones');

    if (mostrandoDesde) mostrandoDesde.textContent = desde || 0;
    if (mostrandoHasta) mostrandoHasta.textContent = hasta || 0;
    if (totalRazonesSpan) totalRazonesSpan.textContent = total || 0;

    // Actualizar botones
    const btnAnterior = document.getElementById('btn-pagina-anterior');
    const btnSiguiente = document.getElementById('btn-pagina-siguiente');

    if (btnAnterior) {
        btnAnterior.disabled = pagina_actual === 1;
        btnAnterior.onclick = () => {
            if (pagina_actual > 1) {
                paginaActual = pagina_actual - 1;
                cargarRazones();
            }
        };
    }
    if (btnSiguiente) {
        btnSiguiente.disabled = pagina_actual >= ultima_pagina;
        btnSiguiente.onclick = () => {
            if (pagina_actual < ultima_pagina) {
                paginaActual = pagina_actual + 1;
                cargarRazones();
            }
        };
    }

    // Generar números de página
    const numerosPaginas = document.getElementById('numeros-paginas');
    if (numerosPaginas && ultima_pagina > 0) {
        numerosPaginas.innerHTML = '';
        
        if (ultima_pagina <= 7) {
            for (let i = 1; i <= ultima_pagina; i++) {
                numerosPaginas.appendChild(crearBotonPagina(i, i === pagina_actual));
            }
        } else {
            if (pagina_actual <= 3) {
                for (let i = 1; i <= 4; i++) {
                    numerosPaginas.appendChild(crearBotonPagina(i, i === pagina_actual));
                }
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                numerosPaginas.appendChild(crearBotonPagina(ultima_pagina, false));
            } else if (pagina_actual >= ultima_pagina - 2) {
                numerosPaginas.appendChild(crearBotonPagina(1, false));
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                for (let i = ultima_pagina - 3; i <= ultima_pagina; i++) {
                    numerosPaginas.appendChild(crearBotonPagina(i, i === pagina_actual));
                }
            } else {
                numerosPaginas.appendChild(crearBotonPagina(1, false));
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                for (let i = pagina_actual - 1; i <= pagina_actual + 1; i++) {
                    numerosPaginas.appendChild(crearBotonPagina(i, i === pagina_actual));
                }
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                numerosPaginas.appendChild(crearBotonPagina(ultima_pagina, false));
            }
        }
    }
}

/**
 * Crea un botón de número de página
 */
function crearBotonPagina(numero, activo) {
    const button = document.createElement('button');
    button.className = `px-4 py-2 text-sm font-medium rounded-lg transition ${
        activo 
            ? 'bg-blue-500 text-white' 
            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
    }`;
    button.textContent = numero;
    button.addEventListener('click', () => {
        paginaActual = numero;
        cargarRazones();
    });
    return button;
}

/**
 * Muestra un mensaje de error en un campo específico
 */
function mostrarError(idError, mensaje) {
    const errorElement = document.getElementById(idError);
    if (errorElement) {
        errorElement.textContent = mensaje;
        errorElement.classList.remove('hidden');
    }
}

/**
 * Maneja los errores de validación del servidor
 */
function manejarErroresValidacion(errors, prefijo) {
    if (!errors) return;

    for (const [campo, mensajes] of Object.entries(errors)) {
        const errorId = `error-${prefijo}${campo}`;
        const mensaje = Array.isArray(mensajes) ? mensajes[0] : mensajes;
        mostrarError(errorId, mensaje);
    }
}

/**
 * Maneja errores de respuesta HTTP
 */
function manejarErrorRespuesta(response, data) {
    if (response.status === 401) {
        mostrarMensajeGlobal('error', 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    } else if (response.status === 403) {
        mostrarMensajeGlobal('error', data.message || 'No tienes permisos para realizar esta acción.');
    } else if (response.status === 422) {
        // Errores de validación ya manejados en las funciones específicas
        mostrarMensajeGlobal('error', data.message || 'Los datos proporcionados no son válidos.');
    } else if (response.status === 500) {
        mostrarMensajeGlobal('error', 'Ocurrió un error en el servidor. Por favor, intenta nuevamente más tarde.');
    } else {
        mostrarMensajeGlobal('error', data.message || 'Ocurrió un error. Por favor, intenta nuevamente.');
    }
}

/**
 * Muestra un mensaje global en la parte superior
 */
function mostrarMensajeGlobal(tipo, mensaje) {
    const contenedor = document.getElementById('mensaje-global');
    if (contenedor) {
        const clase = tipo === 'success' 
            ? 'bg-green-100 border border-green-400 text-green-700' 
            : 'bg-red-100 border border-red-400 text-red-700';
        contenedor.className = `p-4 rounded-lg ${clase}`;
        contenedor.textContent = mensaje;
        contenedor.classList.remove('hidden');
        
        // Ocultar después de 5 segundos
        setTimeout(() => {
            contenedor.classList.add('hidden');
        }, 5000);
    }
}

/**
 * Muestra un mensaje en un contenedor específico
 */
function mostrarMensaje(tipo, mensaje, contenedorId) {
    if (contenedorId) {
        const contenedor = document.getElementById(contenedorId);
        if (contenedor) {
            const clase = tipo === 'success' 
                ? 'bg-green-100 border border-green-400 text-green-700' 
                : 'bg-red-100 border border-red-400 text-red-700';
            contenedor.className = `p-3 rounded-lg ${clase}`;
            contenedor.textContent = mensaje;
            contenedor.classList.remove('hidden');
        }
    }
}

/**
 * Oculta un mensaje
 */
function ocultarMensaje(contenedorId) {
    const contenedor = document.getElementById(contenedorId);
    if (contenedor) {
        contenedor.classList.add('hidden');
    }
}

/**
 * Muestra/oculta el estado de carga general
 */
function mostrarLoading(mostrar) {
    const tabla = document.getElementById('tabla-razones-body');
    if (tabla && mostrar) {
        tabla.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Cargando...</td></tr>';
    }
}

/**
 * Muestra/oculta el estado de carga en un formulario
 */
function mostrarLoadingFormulario(formId, mostrar) {
    const form = document.getElementById(formId);
    if (!form) return;

    const botones = form.querySelectorAll('button[type="submit"]');
    botones.forEach(boton => {
        boton.disabled = mostrar;
        if (mostrar) {
            boton.dataset.originalText = boton.textContent;
            boton.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span>Procesando...';
        } else {
            boton.textContent = boton.dataset.originalText || boton.textContent;
        }
    });

    // Deshabilitar todos los inputs
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.disabled = mostrar;
    });
}
