// Gestión de Consultas y Descargas - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de consultas y descargas en el frontend

// Variables globales
let empleados = []; // Todos los empleados cargados para obtener departamentos

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
async function inicializarDatos() {
    try {
        await cargarEmpleados();
        poblarSelectorDepartamentos();
    } catch (error) {
        console.error('Error al inicializar datos:', error);
        mostrarMensajeGlobal('error', 'Error al cargar los datos iniciales. Por favor, intenta nuevamente.');
    }
}

/**
 * Inicializa todos los event listeners
 */
function inicializarEventListeners() {
    // Botón Consultar
    const btnConsultar = document.getElementById('btn-consultar');
    if (btnConsultar) {
        btnConsultar.addEventListener('click', manejarConsultar);
    }

    // Botón Descargar
    const btnDescargar = document.getElementById('btn-descargar');
    if (btnDescargar) {
        btnDescargar.addEventListener('click', manejarDescargar);
    }
}

/**
 * Carga los empleados desde el servidor para obtener departamentos
 */
async function cargarEmpleados() {
    try {
        // Obtener todos los empleados sin paginación
        const url = new URL('/empleados', window.location.origin);
        url.searchParams.append('por_pagina', 1000); // Obtener muchos empleados

        const response = await fetch(url.toString(), {
            ...fetchConfig('GET'),
            body: null,
        });

        const data = await response.json();

        if (data.success && data.data && data.data.empleados) {
            empleados = data.data.empleados;
        } else {
            empleados = [];
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al cargar empleados:', error);
        empleados = [];
        throw error;
    }
}

/**
 * Extrae los departamentos únicos de los empleados
 */
function obtenerDepartamentosUnicos() {
    const departamentosSet = new Set();
    
    empleados.forEach(empleado => {
        if (empleado.departamento && empleado.departamento.trim() !== '') {
            departamentosSet.add(empleado.departamento);
        }
    });
    
    return Array.from(departamentosSet).sort();
}

/**
 * Pobla el selector de departamentos con las opciones disponibles
 */
function poblarSelectorDepartamentos() {
    const selectDepartamento = document.getElementById('departamento-filtro');
    if (!selectDepartamento) return;
    
    const departamentos = obtenerDepartamentosUnicos();
    
    // Limpiar opciones actuales (excepto "Todos")
    selectDepartamento.innerHTML = '';
    
    // Agregar opción "Todos" como valor por defecto
    const optionTodos = document.createElement('option');
    optionTodos.value = 'todos';
    optionTodos.textContent = 'Todos';
    optionTodos.selected = true;
    selectDepartamento.appendChild(optionTodos);
    
    // Agregar opciones de departamentos
    departamentos.forEach(departamento => {
        const option = document.createElement('option');
        option.value = departamento;
        option.textContent = departamento;
        selectDepartamento.appendChild(option);
    });
}

/**
 * Valida el formulario de filtros
 */
function validarFormulario() {
    const fechaDesde = document.getElementById('fecha-desde').value;
    const fechaHasta = document.getElementById('fecha-hasta').value;
    
    // Limpiar errores previos
    limpiarErrores();
    
    let esValido = true;
    
    // Validar fecha desde
    if (!fechaDesde) {
        mostrarError('error-fecha-desde', 'La fecha desde es requerida');
        esValido = false;
    }
    
    // Validar fecha hasta
    if (!fechaHasta) {
        mostrarError('error-fecha-hasta', 'La fecha hasta es requerida');
        esValido = false;
    }
    
    // Validar que fecha desde <= fecha hasta
    if (fechaDesde && fechaHasta) {
        const fechaDesdeObj = new Date(fechaDesde);
        const fechaHastaObj = new Date(fechaHasta);
        
        if (fechaDesdeObj > fechaHastaObj) {
            mostrarError('error-fecha-hasta', 'La fecha hasta debe ser mayor o igual a la fecha desde');
            esValido = false;
        }
    }
    
    return esValido;
}

/**
 * Limpia los mensajes de error
 */
function limpiarErrores() {
    const errores = document.querySelectorAll('[id^="error-"]');
    errores.forEach(error => {
        error.classList.add('hidden');
        error.textContent = '';
    });
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
 * Maneja el clic en el botón Consultar
 */
async function manejarConsultar() {
    // Validar formulario
    if (!validarFormulario()) {
        mostrarMensajeGlobal('error', 'Por favor, complete correctamente todos los campos requeridos.');
        return;
    }
    
    // Obtener valores del formulario
    const fechaDesde = document.getElementById('fecha-desde').value;
    const fechaHasta = document.getElementById('fecha-hasta').value;
    const departamento = document.getElementById('departamento-filtro').value;
    
    try {
        mostrarLoading(true);
        
        const response = await fetch('/consultas-descargas/consultar', fetchConfig('POST', {
            fecha_desde: fechaDesde,
            fecha_hasta: fechaHasta,
            departamento: departamento === 'todos' ? null : departamento
        }));
        
        const data = await response.json();
        
        if (data.success) {
            mostrarResultados(data.data);
            mostrarMensajeGlobal('success', data.message || 'Consulta realizada exitosamente.');
        } else {
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al consultar:', error);
        mostrarMensajeGlobal('error', 'Error al realizar la consulta. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Maneja el clic en el botón Descargar
 */
async function manejarDescargar() {
    // Validar formulario
    if (!validarFormulario()) {
        mostrarMensajeGlobal('error', 'Por favor, complete correctamente todos los campos requeridos.');
        return;
    }
    
    // Obtener valores del formulario
    const fechaDesde = document.getElementById('fecha-desde').value;
    const fechaHasta = document.getElementById('fecha-hasta').value;
    const departamento = document.getElementById('departamento-filtro').value;
    
    try {
        const params = new URLSearchParams({
            fecha_desde: fechaDesde,
            fecha_hasta: fechaHasta,
            departamento: departamento === 'todos' ? '' : departamento
        });
        
        // Descargar archivo Excel
        window.location.href = '/consultas-descargas/descargar?' + params.toString();
        
        // Mostrar mensaje de éxito
        mostrarMensajeGlobal('success', 'Descargando archivo...');
    } catch (error) {
        console.error('Error al descargar:', error);
        mostrarMensajeGlobal('error', 'Error al descargar el archivo. Por favor, intenta nuevamente.');
    }
}

/**
 * Muestra los resultados en la tabla
 */
function mostrarResultados(resultados) {
    const tbody = document.getElementById('tabla-resultados-body');
    const sinResultados = document.getElementById('sin-resultados');
    
    if (!tbody) return;
    
    if (!resultados || resultados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">No se encontraron resultados</td></tr>';
        if (sinResultados) {
            sinResultados.classList.remove('hidden');
        }
        return;
    }
    
    if (sinResultados) {
        sinResultados.classList.add('hidden');
    }
    
    // Generar filas de la tabla
    tbody.innerHTML = resultados.map(resultado => {
        const fecha = resultado.fecha 
            ? new Date(resultado.fecha + 'T00:00:00').toLocaleDateString('es-ES')
            : 'N/A';
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fecha}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.codigo_empleado || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.nombres || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.apellidos || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.departamento || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.horas_trabajadas || '0'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.horas_ausentes || '0'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${resultado.razon_ausencia || 'N/A'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${resultado.comentarios || ''}</td>
            </tr>
        `;
    }).join('');
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
        let clase = '';
        if (tipo === 'success') {
            clase = 'bg-green-100 border border-green-400 text-green-700';
        } else if (tipo === 'error') {
            clase = 'bg-red-100 border border-red-400 text-red-700';
        } else if (tipo === 'info') {
            clase = 'bg-blue-100 border border-blue-400 text-blue-700';
        } else {
            clase = 'bg-gray-100 border border-gray-400 text-gray-700';
        }
        
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
 * Muestra/oculta el estado de carga
 */
function mostrarLoading(mostrar) {
    const tbody = document.getElementById('tabla-resultados-body');
    if (tbody && mostrar) {
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">Cargando...</td></tr>';
    }
    
    const btnConsultar = document.getElementById('btn-consultar');
    const btnDescargar = document.getElementById('btn-descargar');
    
    if (btnConsultar) {
        btnConsultar.disabled = mostrar;
    }
    if (btnDescargar) {
        btnDescargar.disabled = mostrar;
    }
}

