// Gestión de Usuarios - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de usuarios en el frontend

// Datos mock almacenados (se cargan desde el JSON en la página)
let usuariosData = [];
let usuariosFiltrados = [];
let paginaActual = 1;
const usuariosPorPagina = 10;

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarDatos();
    inicializarEventListeners();
    inicializarPaginacion();
});

/**
 * Carga los datos mock desde el JSON en la página
 */
function inicializarDatos() {
    const dataElement = document.getElementById('usuarios-data');
    if (dataElement) {
        try {
            usuariosData = JSON.parse(dataElement.textContent);
            usuariosFiltrados = [...usuariosData];
            actualizarTabla();
            actualizarPaginacion();
        } catch (error) {
            console.error('Error al cargar datos de usuarios:', error);
        }
    }
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

    // Búsqueda
    const inputBuscar = document.getElementById('buscar-usuario');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', manejarBusqueda);
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
    const usuario = usuariosData.find(u => u.id_usuario == usuarioId);
    if (!usuario) {
        mostrarMensaje('error', 'Usuario no encontrado', 'mensaje-editar');
        return;
    }

    // Llenar el formulario con los datos del usuario
    document.getElementById('editar-id-usuario').value = usuario.id_usuario;
    document.getElementById('editar-nombre-usuario').value = usuario.nombre_usuario || '';
    document.getElementById('editar-nombres').value = usuario.nombres || '';
    document.getElementById('editar-apellidos').value = usuario.apellidos || '';
    document.getElementById('editar-departamento').value = usuario.departamento_trabajo || '';
    document.getElementById('editar-codigo-empleado').value = usuario.codigo_empleado || '';
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
function manejarCrearUsuario(e) {
    e.preventDefault();
    
    limpiarErroresCrear();
    
    const formData = {
        nombre_usuario: document.getElementById('crear-nombre-usuario').value.trim(),
        nombres: document.getElementById('crear-nombres').value.trim(),
        apellidos: document.getElementById('crear-apellidos').value.trim(),
        departamento_trabajo: document.getElementById('crear-departamento').value.trim(),
        codigo_empleado: document.getElementById('crear-codigo-empleado').value.trim(),
        clave: document.getElementById('crear-clave').value,
        confirmar_clave: document.getElementById('crear-confirmar-clave').value
    };

    // Validaciones
    let esValido = true;

    if (!formData.nombre_usuario) {
        mostrarError('error-crear-nombre-usuario', 'Este campo es requerido');
        esValido = false;
    }

    if (!formData.nombres) {
        mostrarError('error-crear-nombres', 'Este campo es requerido');
        esValido = false;
    }

    if (!formData.apellidos) {
        mostrarError('error-crear-apellidos', 'Este campo es requerido');
        esValido = false;
    }

    if (!formData.clave) {
        mostrarError('error-crear-clave', 'Este campo es requerido');
        esValido = false;
    }

    if (formData.clave && formData.clave !== formData.confirmar_clave) {
        mostrarError('error-crear-confirmar-clave', 'Las contraseñas no coinciden');
        esValido = false;
    }

    if (!esValido) {
        return;
    }

    // Simular creación (en el backend real, aquí se haría la petición)
    const nuevoUsuario = {
        id_usuario: usuariosData.length > 0 ? Math.max(...usuariosData.map(u => u.id_usuario)) + 1 : 1,
        nombre_usuario: formData.nombre_usuario,
        nombres: formData.nombres,
        apellidos: formData.apellidos,
        departamento_trabajo: formData.departamento_trabajo || null,
        codigo_empleado: formData.codigo_empleado || null,
        created_at: new Date().toISOString()
    };

    usuariosData.push(nuevoUsuario);
    usuariosFiltrados = [...usuariosData];
    paginaActual = Math.ceil(usuariosFiltrados.length / usuariosPorPagina);
    
    actualizarTabla();
    actualizarPaginacion();
    cerrarModalCrearUsuario();
    
    mostrarMensaje('success', 'Usuario creado exitosamente', 'mensaje-crear');
    setTimeout(() => {
        mostrarMensaje('success', 'Usuario creado exitosamente. Recuerde que los cambios se perderán al recargar la página.', null);
    }, 100);
}

/**
 * Maneja el envío del formulario de editar usuario
 */
function manejarEditarUsuario(e) {
    e.preventDefault();
    
    limpiarErroresEditar();
    
    const idUsuario = parseInt(document.getElementById('editar-id-usuario').value);
    const formData = {
        nombre_usuario: document.getElementById('editar-nombre-usuario').value.trim(),
        nombres: document.getElementById('editar-nombres').value.trim(),
        apellidos: document.getElementById('editar-apellidos').value.trim(),
        departamento_trabajo: document.getElementById('editar-departamento').value.trim(),
        codigo_empleado: document.getElementById('editar-codigo-empleado').value.trim(),
        clave: document.getElementById('editar-clave').value,
        confirmar_clave: document.getElementById('editar-confirmar-clave').value
    };

    // Validaciones
    let esValido = true;

    if (!formData.nombre_usuario) {
        mostrarError('error-editar-nombre-usuario', 'Este campo es requerido');
        esValido = false;
    }

    if (!formData.nombres) {
        mostrarError('error-editar-nombres', 'Este campo es requerido');
        esValido = false;
    }

    if (!formData.apellidos) {
        mostrarError('error-editar-apellidos', 'Este campo es requerido');
        esValido = false;
    }

    // Si se ingresó una nueva contraseña, validar que coincidan
    if (formData.clave) {
        if (formData.clave !== formData.confirmar_clave) {
            mostrarError('error-editar-confirmar-clave', 'Las contraseñas no coinciden');
            esValido = false;
        }
    }

    if (!esValido) {
        return;
    }

    // Simular actualización (en el backend real, aquí se haría la petición)
    const indice = usuariosData.findIndex(u => u.id_usuario === idUsuario);
    if (indice !== -1) {
        usuariosData[indice] = {
            ...usuariosData[indice],
            nombre_usuario: formData.nombre_usuario,
            nombres: formData.nombres,
            apellidos: formData.apellidos,
            departamento_trabajo: formData.departamento_trabajo || null,
            codigo_empleado: formData.codigo_empleado || null
        };

        usuariosFiltrados = [...usuariosData];
        actualizarTabla();
        cerrarModalEditarUsuario();
        
        mostrarMensaje('success', 'Usuario actualizado exitosamente. Recuerde que los cambios se perderán al recargar la página.', null);
    }
}

/**
 * Confirma y elimina un usuario
 */
function confirmarEliminar(usuarioId, nombreUsuario) {
    if (confirm(`¿Está seguro de que desea eliminar al usuario "${nombreUsuario}"?\n\nEsta acción no se puede deshacer.`)) {
        // Simular eliminación (en el backend real, aquí se haría la petición)
        usuariosData = usuariosData.filter(u => u.id_usuario != usuarioId);
        usuariosFiltrados = usuariosFiltrados.filter(u => u.id_usuario != usuarioId);
        
        // Ajustar página actual si es necesario
        const totalPaginas = Math.ceil(usuariosFiltrados.length / usuariosPorPagina);
        if (paginaActual > totalPaginas && totalPaginas > 0) {
            paginaActual = totalPaginas;
        } else if (totalPaginas === 0) {
            paginaActual = 1;
        }
        
        actualizarTabla();
        actualizarPaginacion();
        
        mostrarMensaje('success', 'Usuario eliminado exitosamente. Recuerde que los cambios se perderán al recargar la página.', null);
    }
}

/**
 * Maneja la búsqueda de usuarios
 */
function manejarBusqueda(e) {
    const termino = e.target.value.toLowerCase().trim();
    
    if (termino === '') {
        usuariosFiltrados = [...usuariosData];
    } else {
        usuariosFiltrados = usuariosData.filter(usuario => {
            return (
                usuario.nombre_usuario?.toLowerCase().includes(termino) ||
                usuario.nombres?.toLowerCase().includes(termino) ||
                usuario.apellidos?.toLowerCase().includes(termino) ||
                usuario.departamento_trabajo?.toLowerCase().includes(termino) ||
                usuario.codigo_empleado?.toLowerCase().includes(termino)
            );
        });
    }
    
    paginaActual = 1;
    actualizarTabla();
    actualizarPaginacion();
}

/**
 * Actualiza la tabla de usuarios con los datos filtrados y paginados
 */
function actualizarTabla() {
    const tbody = document.getElementById('tabla-usuarios-body');
    const sinResultados = document.getElementById('sin-resultados');
    
    if (!tbody) return;

    // Calcular usuarios para la página actual
    const inicio = (paginaActual - 1) * usuariosPorPagina;
    const fin = inicio + usuariosPorPagina;
    const usuariosPagina = usuariosFiltrados.slice(inicio, fin);

    if (usuariosFiltrados.length === 0) {
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
    tbody.innerHTML = usuariosPagina.map(usuario => {
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
 * Inicializa la paginación
 */
function inicializarPaginacion() {
    const btnAnterior = document.getElementById('btn-pagina-anterior');
    const btnSiguiente = document.getElementById('btn-pagina-siguiente');

    if (btnAnterior) {
        btnAnterior.addEventListener('click', () => {
            if (paginaActual > 1) {
                paginaActual--;
                actualizarTabla();
                actualizarPaginacion();
            }
        });
    }

    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', () => {
            const totalPaginas = Math.ceil(usuariosFiltrados.length / usuariosPorPagina);
            if (paginaActual < totalPaginas) {
                paginaActual++;
                actualizarTabla();
                actualizarPaginacion();
            }
        });
    }
}

/**
 * Actualiza la información y controles de paginación
 */
function actualizarPaginacion() {
    const totalUsuarios = usuariosFiltrados.length;
    const totalPaginas = Math.ceil(totalUsuarios / usuariosPorPagina);
    const inicio = totalUsuarios > 0 ? (paginaActual - 1) * usuariosPorPagina + 1 : 0;
    const fin = Math.min(paginaActual * usuariosPorPagina, totalUsuarios);

    // Actualizar texto de información
    const mostrandoDesde = document.getElementById('mostrando-desde');
    const mostrandoHasta = document.getElementById('mostrando-hasta');
    const totalUsuariosSpan = document.getElementById('total-usuarios');

    if (mostrandoDesde) mostrandoDesde.textContent = inicio;
    if (mostrandoHasta) mostrandoHasta.textContent = fin;
    if (totalUsuariosSpan) totalUsuariosSpan.textContent = totalUsuarios;

    // Actualizar botones
    const btnAnterior = document.getElementById('btn-pagina-anterior');
    const btnSiguiente = document.getElementById('btn-pagina-siguiente');

    if (btnAnterior) {
        btnAnterior.disabled = paginaActual === 1;
    }
    if (btnSiguiente) {
        btnSiguiente.disabled = paginaActual >= totalPaginas || totalPaginas === 0;
    }

    // Generar números de página
    const numerosPaginas = document.getElementById('numeros-paginas');
    if (numerosPaginas) {
        numerosPaginas.innerHTML = '';
        
        if (totalPaginas <= 7) {
            // Mostrar todas las páginas si son 7 o menos
            for (let i = 1; i <= totalPaginas; i++) {
                numerosPaginas.appendChild(crearBotonPagina(i, i === paginaActual));
            }
        } else {
            // Mostrar páginas con elipsis
            if (paginaActual <= 3) {
                for (let i = 1; i <= 4; i++) {
                    numerosPaginas.appendChild(crearBotonPagina(i, i === paginaActual));
                }
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                numerosPaginas.appendChild(crearBotonPagina(totalPaginas, false));
            } else if (paginaActual >= totalPaginas - 2) {
                numerosPaginas.appendChild(crearBotonPagina(1, false));
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                for (let i = totalPaginas - 3; i <= totalPaginas; i++) {
                    numerosPaginas.appendChild(crearBotonPagina(i, i === paginaActual));
                }
            } else {
                numerosPaginas.appendChild(crearBotonPagina(1, false));
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                for (let i = paginaActual - 1; i <= paginaActual + 1; i++) {
                    numerosPaginas.appendChild(crearBotonPagina(i, i === paginaActual));
                }
                numerosPaginas.innerHTML += '<span class="px-2 py-2 text-gray-500">...</span>';
                numerosPaginas.appendChild(crearBotonPagina(totalPaginas, false));
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
        actualizarTabla();
        actualizarPaginacion();
    });
    return button;
}

/**
 * Muestra un mensaje de error
 */
function mostrarError(idError, mensaje) {
    const errorElement = document.getElementById(idError);
    if (errorElement) {
        errorElement.textContent = mensaje;
        errorElement.classList.remove('hidden');
    }
}

/**
 * Muestra un mensaje de éxito o error en un contenedor
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
    } else {
        // Mostrar mensaje temporal en la parte superior
        const mensajeDiv = document.createElement('div');
        const clase = tipo === 'success' 
            ? 'bg-green-100 border border-green-400 text-green-700' 
            : 'bg-red-100 border border-red-400 text-red-700';
        mensajeDiv.className = `fixed top-20 right-6 z-50 p-4 rounded-lg shadow-lg ${clase}`;
        mensajeDiv.textContent = mensaje;
        document.body.appendChild(mensajeDiv);
        
        setTimeout(() => {
            mensajeDiv.remove();
        }, 5000);
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

