// Gestión de Empleados - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de empleados en el frontend

// Variables globales
let paginaActual = 1;
let terminoBusqueda = '';
let timeoutBusqueda = null;
const empleadosPorPagina = 15;
let idContador = 1; // Contador para generar IDs únicos

// Datos mockeados de empleados
let empleados = [
    {
        id: idContador++,
        nombres: 'Juan',
        apellidos: 'Pérez',
        departamento: 'Recursos Humanos',
        codigo_empleado: 'EMP001',
        fecha_ingreso: '2020-01-15'
    },
    {
        id: idContador++,
        nombres: 'María',
        apellidos: 'González',
        departamento: 'Ventas',
        codigo_empleado: 'EMP002',
        fecha_ingreso: '2020-03-20'
    },
    {
        id: idContador++,
        nombres: 'Carlos',
        apellidos: 'Rodríguez',
        departamento: 'Tecnología',
        codigo_empleado: 'EMP003',
        fecha_ingreso: '2021-06-10'
    }
    
];

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarDatos();
    inicializarEventListeners();
});

/**
 * Carga los datos iniciales
 */
function inicializarDatos() {
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
 * Carga y muestra los empleados en la tabla
 */
function cargarEmpleados() {
    // Filtrar empleados según el término de búsqueda
    let empleadosFiltrados = empleados;
    
    if (terminoBusqueda.trim() !== '') {
        const busqueda = terminoBusqueda.toLowerCase();
        empleadosFiltrados = empleados.filter(empleado => {
            const nombres = (empleado.nombres || '').toLowerCase();
            const apellidos = (empleado.apellidos || '').toLowerCase();
            const departamento = (empleado.departamento || '').toLowerCase();
            const codigo = (empleado.codigo_empleado || '').toLowerCase();
            
            return nombres.includes(busqueda) || 
                   apellidos.includes(busqueda) || 
                   departamento.includes(busqueda) || 
                   codigo.includes(busqueda);
        });
    }

    // Calcular paginación
    const total = empleadosFiltrados.length;
    const ultimaPagina = Math.ceil(total / empleadosPorPagina) || 1;
    const desde = total > 0 ? ((paginaActual - 1) * empleadosPorPagina) + 1 : 0;
    const hasta = Math.min(paginaActual * empleadosPorPagina, total);

    // Obtener empleados de la página actual
    const inicio = (paginaActual - 1) * empleadosPorPagina;
    const fin = inicio + empleadosPorPagina;
    const empleadosPagina = empleadosFiltrados.slice(inicio, fin);

    // Actualizar tabla
    actualizarTabla(empleadosPagina);

    // Actualizar paginación
    actualizarPaginacion({
        pagina_actual: paginaActual,
        ultima_pagina: ultimaPagina,
        total: total,
        desde: desde,
        hasta: hasta
    });
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
    // Buscar el empleado en el array
    const empleado = empleados.find(emp => emp.id === empleadoId);
    
    if (!empleado) {
        mostrarMensajeGlobal('error', 'Empleado no encontrado.');
        return;
    }

    // Llenar el formulario
    document.getElementById('editar-id-empleado').value = empleado.id;
    document.getElementById('editar-nombres').value = empleado.nombres || '';
    document.getElementById('editar-apellidos').value = empleado.apellidos || '';
    document.getElementById('editar-departamento').value = empleado.departamento || '';
    document.getElementById('editar-codigo-empleado').value = empleado.codigo_empleado || '';
    document.getElementById('editar-fecha-ingreso').value = empleado.fecha_ingreso || '';

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
function manejarCrearEmpleado(e) {
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

    // Crear nuevo empleado
    const nuevoEmpleado = {
        id: idContador++,
        nombres: formData.nombres,
        apellidos: formData.apellidos,
        departamento: formData.departamento,
        codigo_empleado: formData.codigo_empleado,
        fecha_ingreso: formData.fecha_ingreso
    };

    // Agregar al array
    empleados.push(nuevoEmpleado);

    // Mostrar mensaje de éxito
    mostrarMensajeGlobal('success', 'Empleado creado exitosamente.');
    
    // Cerrar modal y recargar lista
    cerrarModalCrearEmpleado();
    paginaActual = 1; // Volver a la primera página
    cargarEmpleados();
}

/**
 * Maneja el envío del formulario de editar empleado
 */
function manejarEditarEmpleado(e) {
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

    // Buscar y actualizar empleado
    const indice = empleados.findIndex(emp => emp.id === idEmpleado);
    
    if (indice === -1) {
        mostrarMensajeGlobal('error', 'Empleado no encontrado.');
        return;
    }

    // Actualizar datos
    empleados[indice] = {
        ...empleados[indice],
        nombres: formData.nombres,
        apellidos: formData.apellidos,
        departamento: formData.departamento,
        codigo_empleado: formData.codigo_empleado,
        fecha_ingreso: formData.fecha_ingreso
    };

    // Mostrar mensaje de éxito
    mostrarMensajeGlobal('success', 'Empleado actualizado exitosamente.');
    
    // Cerrar modal y recargar lista
    cerrarModalEditarEmpleado();
    cargarEmpleados();
}

/**
 * Confirma y elimina un empleado
 */
function confirmarEliminar(empleadoId, nombres, apellidos) {
    const nombreCompleto = `${nombres} ${apellidos}`.trim();
    
    if (!confirm(`¿Está seguro de que desea eliminar al empleado "${nombreCompleto}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }

    // Buscar y eliminar empleado
    const indice = empleados.findIndex(emp => emp.id === empleadoId);
    
    if (indice === -1) {
        mostrarMensajeGlobal('error', 'Empleado no encontrado.');
        return;
    }

    // Eliminar del array
    empleados.splice(indice, 1);

    // Ajustar página si es necesario
    const empleadosFiltrados = obtenerEmpleadosFiltrados();
    const total = empleadosFiltrados.length;
    const ultimaPagina = Math.ceil(total / empleadosPorPagina) || 1;
    
    if (paginaActual > ultimaPagina && ultimaPagina > 0) {
        paginaActual = ultimaPagina;
    }

    // Mostrar mensaje de éxito
    mostrarMensajeGlobal('success', 'Empleado eliminado exitosamente.');
    
    // Recargar lista
    cargarEmpleados();
}

/**
 * Obtiene los empleados filtrados según el término de búsqueda
 */
function obtenerEmpleadosFiltrados() {
    if (terminoBusqueda.trim() === '') {
        return empleados;
    }

    const busqueda = terminoBusqueda.toLowerCase();
    return empleados.filter(empleado => {
        const nombres = (empleado.nombres || '').toLowerCase();
        const apellidos = (empleado.apellidos || '').toLowerCase();
        const departamento = (empleado.departamento || '').toLowerCase();
        const codigo = (empleado.codigo_empleado || '').toLowerCase();
        
        return nombres.includes(busqueda) || 
               apellidos.includes(busqueda) || 
               departamento.includes(busqueda) || 
               codigo.includes(busqueda);
    });
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
            <tr class="empleado-fila hover:bg-gray-50" data-empleado-id="${empleado.id}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.nombres || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.apellidos || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.departamento || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.codigo_empleado || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fecha}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                        class="btn-editar text-blue-600 hover:text-blue-900 mr-3" 
                        data-empleado-id="${empleado.id}"
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
                        data-empleado-id="${empleado.id}"
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

