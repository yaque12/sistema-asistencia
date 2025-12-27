// Gestión de Empleados - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de empleados en el frontend

// Variables globales
let paginaActual = 1;
let terminoBusqueda = '';
let timeoutBusqueda = null;
const empleadosPorPagina = 15;

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
    // Cargar empleados iniciales desde el servidor
    cargarEmpleados();
}

/**
 * Inicializa todos los event listeners
 */
function inicializarEventListeners() {
    // Botón nuevo empleado
    const btnNuevoEmpleado = document.getElementById('btn-nuevo-empleado');
    if (btnNuevoEmpleado) {
        btnNuevoEmpleado.addEventListener('click', abrirModalCrear);
    }

    // Botones cerrar modales
    const cerrarModalCrear = document.getElementById('cerrar-modal-crear');
    const cerrarModalEditar = document.getElementById('cerrar-modal-editar');
    const cancelarCrear = document.getElementById('cancelar-crear-empleado');
    const cancelarEditar = document.getElementById('cancelar-editar-empleado');

    if (cerrarModalCrear) {
        cerrarModalCrear.addEventListener('click', cerrarModalCrearEmpleado);
    }
    if (cerrarModalEditar) {
        cerrarModalEditar.addEventListener('click', cerrarModalEditarEmpleado);
    }
    if (cancelarCrear) {
        cancelarCrear.addEventListener('click', cerrarModalCrearEmpleado);
    }
    if (cancelarEditar) {
        cancelarEditar.addEventListener('click', cerrarModalEditarEmpleado);
    }

    // Cerrar modal al hacer clic fuera
    const modalCrear = document.getElementById('modal-crear-empleado');
    const modalEditar = document.getElementById('modal-editar-empleado');
    
    if (modalCrear) {
        modalCrear.addEventListener('click', function(e) {
            if (e.target === modalCrear) {
                cerrarModalCrearEmpleado();
            }
        });
    }
    
    if (modalEditar) {
        modalEditar.addEventListener('click', function(e) {
            if (e.target === modalEditar) {
                cerrarModalEditarEmpleado();
            }
        });
    }

    // Formularios
    const formCrear = document.getElementById('form-crear-empleado');
    const formEditar = document.getElementById('form-editar-empleado');

    if (formCrear) {
        formCrear.addEventListener('submit', manejarCrearEmpleado);
    }
    if (formEditar) {
        formEditar.addEventListener('submit', manejarEditarEmpleado);
    }

    // Búsqueda con debounce
    const inputBuscar = document.getElementById('buscar-empleado');
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
            const empleadoId = parseInt(e.target.getAttribute('data-empleado-id'));
            abrirModalEditar(empleadoId, e.target);
        }
        if (e.target.classList.contains('btn-eliminar')) {
            e.preventDefault();
            const empleadoId = parseInt(e.target.getAttribute('data-empleado-id'));
            const nombres = e.target.getAttribute('data-nombres');
            const apellidos = e.target.getAttribute('data-apellidos');
            confirmarEliminar(empleadoId, nombres, apellidos);
        }
    });
}

/**
 * Carga los empleados desde el servidor
 */
async function cargarEmpleados() {
    try {
        mostrarLoading(true);
        
        const url = new URL('/empleados', window.location.origin);
        url.searchParams.append('buscar', terminoBusqueda);
        url.searchParams.append('pagina', paginaActual);
        url.searchParams.append('por_pagina', empleadosPorPagina);

        const response = await fetch(url.toString(), {
            ...fetchConfig('GET'),
            body: null,
        });

        const data = await response.json();

        if (data.success) {
            actualizarTabla(data.data.empleados);
            actualizarPaginacion(data.data.paginacion);
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al cargar empleados:', error);
        mostrarMensajeGlobal('error', 'Error al cargar los empleados. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Abre el modal para crear un nuevo empleado
 */
function abrirModalCrear() {
    const modal = document.getElementById('modal-crear-empleado');
    if (modal) {
        limpiarFormularioCrear();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra el modal de crear empleado
 */
function cerrarModalCrearEmpleado() {
    const modal = document.getElementById('modal-crear-empleado');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        limpiarFormularioCrear();
    }
}

/**
 * Abre el modal para editar un empleado
 */
function abrirModalEditar(empleadoId, boton) {
    // Obtener datos del empleado desde los atributos del botón
    const nombres = boton.getAttribute('data-nombres');
    const apellidos = boton.getAttribute('data-apellidos');
    const departamento = boton.getAttribute('data-departamento');
    const codigoEmpleado = boton.getAttribute('data-codigo-empleado');
    const fechaIngreso = boton.getAttribute('data-fecha-ingreso');

    // Llenar el formulario
    document.getElementById('editar-id-empleado').value = empleadoId;
    document.getElementById('editar-nombres').value = nombres || '';
    document.getElementById('editar-apellidos').value = apellidos || '';
    document.getElementById('editar-departamento').value = departamento || '';
    document.getElementById('editar-codigo-empleado').value = codigoEmpleado || '';
    document.getElementById('editar-fecha-ingreso').value = fechaIngreso || '';

    // Ocultar mensajes de error
    limpiarErroresEditar();

    const modal = document.getElementById('modal-editar-empleado');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra el modal de editar empleado
 */
function cerrarModalEditarEmpleado() {
    const modal = document.getElementById('modal-editar-empleado');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        limpiarFormularioEditar();
    }
}

/**
 * Limpia el formulario de crear empleado
 */
function limpiarFormularioCrear() {
    const form = document.getElementById('form-crear-empleado');
    if (form) {
        form.reset();
        limpiarErroresCrear();
        ocultarMensaje('mensaje-crear');
    }
}

/**
 * Limpia el formulario de editar empleado
 */
function limpiarFormularioEditar() {
    const form = document.getElementById('form-editar-empleado');
    if (form) {
        form.reset();
        limpiarErroresEditar();
        ocultarMensaje('mensaje-editar');
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
 * Limpia los mensajes de error del formulario editar
 */
function limpiarErroresEditar() {
    const errores = document.querySelectorAll('[id^="error-editar-"]');
    errores.forEach(error => {
        error.classList.add('hidden');
    });
}

/**
 * Maneja el envío del formulario de crear empleado
 */
async function manejarCrearEmpleado(e) {
    e.preventDefault();
    
    limpiarErroresCrear();
    
    const formData = {
        nombres: document.getElementById('crear-nombres').value.trim(),
        apellidos: document.getElementById('crear-apellidos').value.trim(),
        departamento: document.getElementById('crear-departamento').value.trim() || null,
        codigo_empleado: document.getElementById('crear-codigo-empleado').value.trim() || null,
        fecha_ingreso: document.getElementById('crear-fecha-ingreso').value.trim()
    };

    // Validación básica del frontend
    if (!formData.nombres || !formData.apellidos || !formData.fecha_ingreso) {
        mostrarMensajeGlobal('error', 'Por favor, complete todos los campos requeridos.');
        if (!formData.nombres) {
            mostrarError('error-crear-nombres', 'Este campo es requerido');
        }
        if (!formData.apellidos) {
            mostrarError('error-crear-apellidos', 'Este campo es requerido');
        }
        if (!formData.fecha_ingreso) {
            mostrarError('error-crear-fecha-ingreso', 'Este campo es requerido');
        }
        return;
    }

    try {
        mostrarLoadingFormulario('form-crear-empleado', true);

        const response = await fetch('/empleados', fetchConfig('POST', formData));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Empleado creado exitosamente.');
            cerrarModalCrearEmpleado();
            paginaActual = 1; // Volver a la primera página
            cargarEmpleados(); // Recargar la lista
        } else {
            manejarErroresValidacion(data.errors, 'crear-');
            mostrarMensaje('error', data.message || 'Error al crear el empleado.', 'mensaje-crear');
        }
    } catch (error) {
        console.error('Error al crear empleado:', error);
        mostrarMensajeGlobal('error', 'Error al crear el empleado. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingFormulario('form-crear-empleado', false);
    }
}

/**
 * Maneja el envío del formulario de editar empleado
 */
async function manejarEditarEmpleado(e) {
    e.preventDefault();
    
    limpiarErroresEditar();
    
    const idEmpleado = parseInt(document.getElementById('editar-id-empleado').value);
    const formData = {
        nombres: document.getElementById('editar-nombres').value.trim(),
        apellidos: document.getElementById('editar-apellidos').value.trim(),
        departamento: document.getElementById('editar-departamento').value.trim() || null,
        codigo_empleado: document.getElementById('editar-codigo-empleado').value.trim() || null,
        fecha_ingreso: document.getElementById('editar-fecha-ingreso').value.trim()
    };

    // Validación básica del frontend
    if (!formData.nombres || !formData.apellidos || !formData.fecha_ingreso) {
        mostrarMensajeGlobal('error', 'Por favor, complete todos los campos requeridos.');
        if (!formData.nombres) {
            mostrarError('error-editar-nombres', 'Este campo es requerido');
        }
        if (!formData.apellidos) {
            mostrarError('error-editar-apellidos', 'Este campo es requerido');
        }
        if (!formData.fecha_ingreso) {
            mostrarError('error-editar-fecha-ingreso', 'Este campo es requerido');
        }
        return;
    }

    try {
        mostrarLoadingFormulario('form-editar-empleado', true);

        const response = await fetch(`/empleados/${idEmpleado}`, fetchConfig('PUT', formData));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Empleado actualizado exitosamente.');
            cerrarModalEditarEmpleado();
            cargarEmpleados(); // Recargar la lista
        } else {
            manejarErroresValidacion(data.errors, 'editar-');
            mostrarMensaje('error', data.message || 'Error al actualizar el empleado.', 'mensaje-editar');
        }
    } catch (error) {
        console.error('Error al actualizar empleado:', error);
        mostrarMensajeGlobal('error', 'Error al actualizar el empleado. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingFormulario('form-editar-empleado', false);
    }
}

/**
 * Confirma y elimina un empleado
 */
async function confirmarEliminar(empleadoId, nombres, apellidos) {
    const nombreCompleto = `${nombres} ${apellidos}`.trim();
    
    if (!confirm(`¿Está seguro de que desea eliminar al empleado "${nombreCompleto}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }

    try {
        mostrarLoading(true);

        const response = await fetch(`/empleados/${empleadoId}`, fetchConfig('DELETE'));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Empleado eliminado exitosamente.');
            cargarEmpleados(); // Recargar la lista
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al eliminar empleado:', error);
        mostrarMensajeGlobal('error', 'Error al eliminar el empleado. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Maneja la búsqueda de empleados
 */
function manejarBusqueda(termino) {
    terminoBusqueda = termino.trim();
    paginaActual = 1;
    cargarEmpleados();
}

/**
 * Actualiza la tabla de empleados con los datos recibidos
 */
function actualizarTabla(empleadosPagina) {
    const tbody = document.getElementById('tabla-empleados-body');
    const sinResultados = document.getElementById('sin-resultados');
    
    if (!tbody) return;

    if (!empleadosPagina || empleadosPagina.length === 0) {
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
    tbody.innerHTML = empleadosPagina.map(empleado => {
        const fecha = empleado.fecha_ingreso 
            ? new Date(empleado.fecha_ingreso + 'T00:00:00').toLocaleDateString('es-ES')
            : 'N/A';
        
        return `
            <tr class="empleado-fila hover:bg-gray-50" data-empleado-id="${empleado.id_empleado}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.id_empleado}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.nombres || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.apellidos || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.departamento || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.codigo_empleado || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fecha}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                        class="btn-editar text-blue-600 hover:text-blue-900 mr-3" 
                        data-empleado-id="${empleado.id_empleado}"
                        data-nombres="${empleado.nombres || ''}"
                        data-apellidos="${empleado.apellidos || ''}"
                        data-departamento="${empleado.departamento || ''}"
                        data-codigo-empleado="${empleado.codigo_empleado || ''}"
                        data-fecha-ingreso="${empleado.fecha_ingreso || ''}"
                    >
                        Editar
                    </button>
                    <button 
                        class="btn-eliminar text-red-600 hover:text-red-900" 
                        data-empleado-id="${empleado.id_empleado}"
                        data-nombres="${empleado.nombres || ''}"
                        data-apellidos="${empleado.apellidos || ''}"
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
    const totalEmpleadosSpan = document.getElementById('total-empleados');

    if (mostrandoDesde) mostrandoDesde.textContent = desde || 0;
    if (mostrandoHasta) mostrandoHasta.textContent = hasta || 0;
    if (totalEmpleadosSpan) totalEmpleadosSpan.textContent = total || 0;

    // Actualizar botones
    const btnAnterior = document.getElementById('btn-pagina-anterior');
    const btnSiguiente = document.getElementById('btn-pagina-siguiente');

    if (btnAnterior) {
        btnAnterior.disabled = pagina_actual === 1;
        btnAnterior.onclick = () => {
            if (pagina_actual > 1) {
                paginaActual = pagina_actual - 1;
                cargarEmpleados();
            }
        };
    }
    if (btnSiguiente) {
        btnSiguiente.disabled = pagina_actual >= ultima_pagina;
        btnSiguiente.onclick = () => {
            if (pagina_actual < ultima_pagina) {
                paginaActual = pagina_actual + 1;
                cargarEmpleados();
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
        cargarEmpleados();
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
    // Puedes agregar un spinner o indicador visual aquí
    const tabla = document.getElementById('tabla-empleados-body');
    if (tabla && mostrar) {
        tabla.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Cargando...</td></tr>';
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
