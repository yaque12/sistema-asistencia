// Gestión de Usuarios - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de usuarios en el frontend

// Variables globales
let paginaActual = 1;
let terminoBusqueda = '';
let timeoutBusqueda = null;
const usuariosPorPagina = 15;

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
    // Cargar usuarios iniciales desde el servidor
    cargarUsuarios();
}

/**
 * Inicializa todos los event listeners
 */
function inicializarEventListeners() {
    // Botón nuevo usuario
    const btnNuevoUsuario = document.getElementById('btn-nuevo-usuario');
    if (btnNuevoUsuario) {
        btnNuevoUsuario.addEventListener('click', abrirModalCrear);
    }

    // Botones cerrar modales
    const cerrarModalCrear = document.getElementById('cerrar-modal-crear');
    const cerrarModalEditar = document.getElementById('cerrar-modal-editar');
    const cancelarCrear = document.getElementById('cancelar-crear-usuario');
    const cancelarEditar = document.getElementById('cancelar-editar-usuario');

    if (cerrarModalCrear) {
        cerrarModalCrear.addEventListener('click', cerrarModalCrearUsuario);
    }
    if (cerrarModalEditar) {
        cerrarModalEditar.addEventListener('click', cerrarModalEditarUsuario);
    }
    if (cancelarCrear) {
        cancelarCrear.addEventListener('click', cerrarModalCrearUsuario);
    }
    if (cancelarEditar) {
        cancelarEditar.addEventListener('click', cerrarModalEditarUsuario);
    }

    // Cerrar modal al hacer clic fuera
    const modalCrear = document.getElementById('modal-crear-usuario');
    const modalEditar = document.getElementById('modal-editar-usuario');
    
    if (modalCrear) {
        modalCrear.addEventListener('click', function(e) {
            if (e.target === modalCrear) {
                cerrarModalCrearUsuario();
            }
        });
    }
    
    if (modalEditar) {
        modalEditar.addEventListener('click', function(e) {
            if (e.target === modalEditar) {
                cerrarModalEditarUsuario();
            }
        });
    }

    // Formularios
    const formCrear = document.getElementById('form-crear-usuario');
    const formEditar = document.getElementById('form-editar-usuario');

    if (formCrear) {
        formCrear.addEventListener('submit', manejarCrearUsuario);
    }
    if (formEditar) {
        formEditar.addEventListener('submit', manejarEditarUsuario);
    }

    // Búsqueda con debounce
    const inputBuscar = document.getElementById('buscar-usuario');
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
            const usuarioId = e.target.getAttribute('data-usuario-id');
            abrirModalEditar(usuarioId, e.target);
        }
        if (e.target.classList.contains('btn-eliminar')) {
            e.preventDefault();
            const usuarioId = e.target.getAttribute('data-usuario-id');
            const nombreUsuario = e.target.getAttribute('data-nombre-usuario');
            confirmarEliminar(usuarioId, nombreUsuario);
        }
    });

    // Validación de contraseña en edición
    const inputClaveEditar = document.getElementById('editar-clave');
    const divConfirmarClaveEditar = document.getElementById('div-confirmar-clave-editar');
    if (inputClaveEditar && divConfirmarClaveEditar) {
        inputClaveEditar.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                divConfirmarClaveEditar.classList.remove('hidden');
                document.getElementById('editar-confirmar-clave').required = true;
            } else {
                divConfirmarClaveEditar.classList.add('hidden');
                document.getElementById('editar-confirmar-clave').required = false;
                document.getElementById('editar-confirmar-clave').value = '';
            }
        });
    }
}

/**
 * Carga los usuarios desde el servidor
 */
async function cargarUsuarios() {
    try {
        mostrarLoading(true);
        
        const url = new URL('/usuarios', window.location.origin);
        url.searchParams.append('buscar', terminoBusqueda);
        url.searchParams.append('pagina', paginaActual);
        url.searchParams.append('por_pagina', usuariosPorPagina);

        const response = await fetch(url.toString(), {
            ...fetchConfig('GET'),
            body: null,
        });

        const data = await response.json();

        if (data.success) {
            actualizarTabla(data.data.usuarios);
            actualizarPaginacion(data.data.paginacion);
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        mostrarMensajeGlobal('error', 'Error al cargar los usuarios. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Abre el modal para crear un nuevo usuario
 */
function abrirModalCrear() {
    const modal = document.getElementById('modal-crear-usuario');
    if (modal) {
        limpiarFormularioCrear();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra el modal de crear usuario
 */
function cerrarModalCrearUsuario() {
    const modal = document.getElementById('modal-crear-usuario');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        limpiarFormularioCrear();
    }
}

/**
 * Abre el modal para editar un usuario
 */
function abrirModalEditar(usuarioId, boton) {
    // Obtener datos del usuario desde los atributos del botón
    const nombreUsuario = boton.getAttribute('data-nombre-usuario');
    const nombres = boton.getAttribute('data-nombres');
    const apellidos = boton.getAttribute('data-apellidos');
    const departamento = boton.getAttribute('data-departamento');
    const codigoEmpleado = boton.getAttribute('data-codigo-empleado');

    // Llenar el formulario
    document.getElementById('editar-id-usuario').value = usuarioId;
    document.getElementById('editar-nombre-usuario').value = nombreUsuario || '';
    document.getElementById('editar-nombres').value = nombres || '';
    document.getElementById('editar-apellidos').value = apellidos || '';
    document.getElementById('editar-departamento').value = departamento || '';
    document.getElementById('editar-codigo-empleado').value = codigoEmpleado || '';
    document.getElementById('editar-clave').value = '';
    document.getElementById('editar-confirmar-clave').value = '';
    
    // Ocultar campo de confirmar contraseña
    const divConfirmarClave = document.getElementById('div-confirmar-clave-editar');
    if (divConfirmarClave) {
        divConfirmarClave.classList.add('hidden');
        document.getElementById('editar-confirmar-clave').required = false;
    }

    // Ocultar mensajes de error
    limpiarErroresEditar();

    const modal = document.getElementById('modal-editar-usuario');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra el modal de editar usuario
 */
function cerrarModalEditarUsuario() {
    const modal = document.getElementById('modal-editar-usuario');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        limpiarFormularioEditar();
    }
}

/**
 * Limpia el formulario de crear usuario
 */
function limpiarFormularioCrear() {
    const form = document.getElementById('form-crear-usuario');
    if (form) {
        form.reset();
        limpiarErroresCrear();
        ocultarMensaje('mensaje-crear');
    }
}

/**
 * Limpia el formulario de editar usuario
 */
function limpiarFormularioEditar() {
    const form = document.getElementById('form-editar-usuario');
    if (form) {
        form.reset();
        limpiarErroresEditar();
        ocultarMensaje('mensaje-editar');
        const divConfirmarClave = document.getElementById('div-confirmar-clave-editar');
        if (divConfirmarClave) {
            divConfirmarClave.classList.add('hidden');
        }
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
 * Maneja el envío del formulario de crear usuario
 */
async function manejarCrearUsuario(e) {
    e.preventDefault();
    
    limpiarErroresCrear();
    
    const formData = {
        nombre_usuario: document.getElementById('crear-nombre-usuario').value.trim(),
        nombres: document.getElementById('crear-nombres').value.trim(),
        apellidos: document.getElementById('crear-apellidos').value.trim(),
        departamento_trabajo: document.getElementById('crear-departamento').value.trim() || null,
        codigo_empleado: document.getElementById('crear-codigo-empleado').value.trim() || null,
        clave: document.getElementById('crear-clave').value,
        confirmar_clave: document.getElementById('crear-confirmar-clave').value
    };

    // Validación básica del frontend
    if (!formData.nombre_usuario || !formData.nombres || !formData.apellidos || !formData.clave) {
        mostrarMensajeGlobal('error', 'Por favor, complete todos los campos requeridos.');
        return;
    }

    if (formData.clave !== formData.confirmar_clave) {
        mostrarError('error-crear-confirmar-clave', 'Las contraseñas no coinciden');
        return;
    }

    try {
        mostrarLoadingFormulario('form-crear-usuario', true);

        const response = await fetch('/usuarios', fetchConfig('POST', formData));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Usuario creado exitosamente.');
            cerrarModalCrearUsuario();
            cargarUsuarios(); // Recargar la lista
        } else {
            manejarErroresValidacion(data.errors, 'crear-');
            mostrarMensaje('error', data.message || 'Error al crear el usuario.', 'mensaje-crear');
        }
    } catch (error) {
        console.error('Error al crear usuario:', error);
        mostrarMensajeGlobal('error', 'Error al crear el usuario. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingFormulario('form-crear-usuario', false);
    }
}

/**
 * Maneja el envío del formulario de editar usuario
 */
async function manejarEditarUsuario(e) {
    e.preventDefault();
    
    limpiarErroresEditar();
    
    const idUsuario = document.getElementById('editar-id-usuario').value;
    const formData = {
        nombre_usuario: document.getElementById('editar-nombre-usuario').value.trim(),
        nombres: document.getElementById('editar-nombres').value.trim(),
        apellidos: document.getElementById('editar-apellidos').value.trim(),
        departamento_trabajo: document.getElementById('editar-departamento').value.trim() || null,
        codigo_empleado: document.getElementById('editar-codigo-empleado').value.trim() || null,
    };

    // Si se ingresó una nueva contraseña, agregarla
    const clave = document.getElementById('editar-clave').value.trim();
    if (clave) {
        const confirmarClave = document.getElementById('editar-confirmar-clave').value.trim();
        if (clave !== confirmarClave) {
            mostrarError('error-editar-confirmar-clave', 'Las contraseñas no coinciden');
            return;
        }
        formData.clave = clave;
        formData.confirmar_clave = confirmarClave;
    }

    // Validación básica del frontend
    if (!formData.nombre_usuario || !formData.nombres || !formData.apellidos) {
        mostrarMensajeGlobal('error', 'Por favor, complete todos los campos requeridos.');
        return;
    }

    try {
        mostrarLoadingFormulario('form-editar-usuario', true);

        const response = await fetch(`/usuarios/${idUsuario}`, fetchConfig('PUT', formData));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Usuario actualizado exitosamente.');
            cerrarModalEditarUsuario();
            cargarUsuarios(); // Recargar la lista
        } else {
            manejarErroresValidacion(data.errors, 'editar-');
            mostrarMensaje('error', data.message || 'Error al actualizar el usuario.', 'mensaje-editar');
        }
    } catch (error) {
        console.error('Error al actualizar usuario:', error);
        mostrarMensajeGlobal('error', 'Error al actualizar el usuario. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingFormulario('form-editar-usuario', false);
    }
}

/**
 * Confirma y elimina un usuario
 */
async function confirmarEliminar(usuarioId, nombreUsuario) {
    if (!confirm(`¿Está seguro de que desea eliminar al usuario "${nombreUsuario}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }

    try {
        mostrarLoading(true);

        const response = await fetch(`/usuarios/${usuarioId}`, fetchConfig('DELETE'));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Usuario eliminado exitosamente.');
            cargarUsuarios(); // Recargar la lista
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al eliminar usuario:', error);
        mostrarMensajeGlobal('error', 'Error al eliminar el usuario. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Maneja la búsqueda de usuarios
 */
function manejarBusqueda(termino) {
    terminoBusqueda = termino.trim();
    paginaActual = 1;
    cargarUsuarios();
}

/**
 * Actualiza la tabla de usuarios con los datos recibidos
 */
function actualizarTabla(usuarios) {
    const tbody = document.getElementById('tabla-usuarios-body');
    const sinResultados = document.getElementById('sin-resultados');
    
    if (!tbody) return;

    if (!usuarios || usuarios.length === 0) {
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
    tbody.innerHTML = usuarios.map(usuario => {
        const fecha = usuario.created_at ? new Date(usuario.created_at).toLocaleDateString('es-ES') : 'N/A';
        
        return `
            <tr class="usuario-fila hover:bg-gray-50" data-usuario-id="${usuario.id_usuario}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${usuario.id_usuario}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${usuario.nombre_usuario || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${usuario.nombres || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${usuario.apellidos || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${usuario.departamento_trabajo || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${usuario.codigo_empleado || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fecha}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                        class="btn-editar text-blue-600 hover:text-blue-900 mr-3" 
                        data-usuario-id="${usuario.id_usuario}"
                        data-nombre-usuario="${usuario.nombre_usuario || ''}"
                        data-nombres="${usuario.nombres || ''}"
                        data-apellidos="${usuario.apellidos || ''}"
                        data-departamento="${usuario.departamento_trabajo || ''}"
                        data-codigo-empleado="${usuario.codigo_empleado || ''}"
                    >
                        Editar
                    </button>
                    <button 
                        class="btn-eliminar text-red-600 hover:text-red-900" 
                        data-usuario-id="${usuario.id_usuario}"
                        data-nombre-usuario="${usuario.nombre_usuario || ''}"
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
    const totalUsuariosSpan = document.getElementById('total-usuarios');

    if (mostrandoDesde) mostrandoDesde.textContent = desde || 0;
    if (mostrandoHasta) mostrandoHasta.textContent = hasta || 0;
    if (totalUsuariosSpan) totalUsuariosSpan.textContent = total || 0;

    // Actualizar botones
    const btnAnterior = document.getElementById('btn-pagina-anterior');
    const btnSiguiente = document.getElementById('btn-pagina-siguiente');

    if (btnAnterior) {
        btnAnterior.disabled = pagina_actual === 1;
        btnAnterior.onclick = () => {
            if (pagina_actual > 1) {
                paginaActual = pagina_actual - 1;
                cargarUsuarios();
            }
        };
    }
    if (btnSiguiente) {
        btnSiguiente.disabled = pagina_actual >= ultima_pagina;
        btnSiguiente.onclick = () => {
            if (pagina_actual < ultima_pagina) {
                paginaActual = pagina_actual + 1;
                cargarUsuarios();
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
        cargarUsuarios();
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
    const tabla = document.getElementById('tabla-usuarios-body');
    if (tabla && mostrar) {
        tabla.innerHTML = '<tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">Cargando...</td></tr>';
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
