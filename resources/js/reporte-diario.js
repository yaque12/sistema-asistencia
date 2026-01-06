// Gestión de Reporte Diario - Funcionalidad Frontend
// Este archivo maneja toda la lógica del módulo de reporte diario en el frontend

// Variables globales
let fechaSeleccionada = null;
let departamentoSeleccionado = '';
let empleados = []; // Todos los empleados cargados
let empleadosFiltrados = []; // Empleados filtrados por departamento
let razonesAusentismos = [];
let reporteDiario = {
    fecha: null,
    registros: [] // Array de objetos con {id_empleado, horas_trabajadas, horas_ausentes, id_razon, comentarios}
};

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
    inicializarEventListeners();
});

/**
 * Inicializa todos los event listeners
 */
function inicializarEventListeners() {
    // Selector de fecha
    const inputFecha = document.getElementById('fecha-reporte');
    if (inputFecha) {
        inputFecha.addEventListener('change', manejarCambioFecha);
    }

    // Selector de departamento
    const selectDepartamento = document.getElementById('departamento-reporte');
    if (selectDepartamento) {
        selectDepartamento.addEventListener('change', manejarCambioDepartamento);
    }

    // Botón guardar
    const btnGuardar = document.getElementById('btn-guardar-reporte');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarReporte);
    }

    // Delegación de eventos para botones editar y eliminar
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-editar-fila')) {
            e.preventDefault();
            const empleadoId = parseInt(e.target.getAttribute('data-empleado-id'));
            editarFila(empleadoId);
        }
        if (e.target.classList.contains('btn-eliminar-fila')) {
            e.preventDefault();
            const empleadoId = parseInt(e.target.getAttribute('data-empleado-id'));
            eliminarFila(empleadoId);
        }
    });
}

/**
 * Maneja el cambio de fecha y carga los empleados
 */
async function manejarCambioFecha(e) {
    const fecha = e.target.value;
    
    if (!fecha) {
        mostrarMensajeGlobal('error', 'Por favor, seleccione una fecha.');
        return;
    }

    fechaSeleccionada = fecha;
    reporteDiario.fecha = fecha;
    
    // Resetear departamento seleccionado al cambiar fecha
    departamentoSeleccionado = '';

    try {
        mostrarLoading(true);
        
        // Cargar empleados y razones en paralelo
        await Promise.all([
            cargarEmpleados(),
            cargarRazonesAusentismos()
        ]);
        
        // Cargar reporte por fecha (puede retornar false si la fecha no está generada)
        const puedeMostrarTabla = await cargarReportePorFecha();
        
        // Si la fecha no está generada, limpiar la tabla y mostrar mensaje
        if (!puedeMostrarTabla) {
            const tbody = document.getElementById('tabla-empleados-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">La fecha no está generada</td></tr>';
            }
            return;
        }
        
        // Poblar el selector de departamentos
        poblarSelectorDepartamentos();
        
        // Aplicar filtro de departamento (mostrar todos por defecto)
        filtrarEmpleadosPorDepartamento();
        
        mostrarTablaEmpleados();
    } catch (error) {
        console.error('Error al cargar datos:', error);
        mostrarMensajeGlobal('error', 'Error al cargar los datos. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

/**
 * Maneja el cambio de departamento y filtra los empleados
 */
function manejarCambioDepartamento(e) {
    departamentoSeleccionado = e.target.value;
    
    // Aplicar filtro
    filtrarEmpleadosPorDepartamento();
    
    // Mostrar tabla actualizada
    mostrarTablaEmpleados();
}

/**
 * Carga los empleados desde el servidor
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
            empleadosFiltrados = empleados;
        } else {
            empleados = [];
            empleadosFiltrados = [];
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al cargar empleados:', error);
        empleados = [];
        empleadosFiltrados = [];
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
    const selectDepartamento = document.getElementById('departamento-reporte');
    if (!selectDepartamento) return;
    
    const departamentos = obtenerDepartamentosUnicos();
    
    // Limpiar opciones actuales
    selectDepartamento.innerHTML = '';
    
    // Agregar opción "Todos"
    const optionTodos = document.createElement('option');
    optionTodos.value = '';
    optionTodos.textContent = 'Todos';
    if (!departamentoSeleccionado) {
        optionTodos.selected = true;
    }
    selectDepartamento.appendChild(optionTodos);
    
    // Agregar opciones de departamentos
    departamentos.forEach(departamento => {
        const option = document.createElement('option');
        option.value = departamento;
        option.textContent = departamento;
        if (departamentoSeleccionado === departamento) {
            option.selected = true;
        }
        selectDepartamento.appendChild(option);
    });
}

/**
 * Filtra los empleados por el departamento seleccionado
 */
function filtrarEmpleadosPorDepartamento() {
    if (!departamentoSeleccionado || departamentoSeleccionado === '') {
        empleadosFiltrados = empleados;
    } else {
        empleadosFiltrados = empleados.filter(empleado => 
            empleado.departamento === departamentoSeleccionado
        );
    }
}

/**
 * Carga las razones de ausentismos desde el servidor
 */
async function cargarRazonesAusentismos() {
    try {
        // Obtener todas las razones sin paginación
        const url = new URL('/razones-ausentismos', window.location.origin);
        url.searchParams.append('por_pagina', 1000); // Obtener muchas razones

        const response = await fetch(url.toString(), {
            ...fetchConfig('GET'),
            body: null,
        });

        const data = await response.json();

        if (data.success && data.data && data.data.razones) {
            razonesAusentismos = data.data.razones;
        } else {
            razonesAusentismos = [];
            manejarErrorRespuesta(response, data);
        }
    } catch (error) {
        console.error('Error al cargar razones de ausentismos:', error);
        razonesAusentismos = [];
        throw error;
    }
}

/**
 * Carga el reporte existente para la fecha seleccionada
 */
async function cargarReportePorFecha() {
    if (!fechaSeleccionada) {
        return;
    }

    try {
        const url = new URL('/reporte-diario', window.location.origin);
        url.searchParams.append('fecha', fechaSeleccionada);
        if (departamentoSeleccionado) {
            url.searchParams.append('departamento', departamentoSeleccionado);
        }

        const response = await fetch(url.toString(), {
            ...fetchConfig('GET'),
            body: null,
        });

        const data = await response.json();

        // Si la fecha no está generada, mostrar mensaje y limpiar datos
        if (!data.success && data.message === 'La fecha no está generada') {
            reporteDiario.registros = [];
            mostrarMensajeGlobal('error', data.message);
            return false; // Indicar que no se debe mostrar la tabla
        }

        if (data.success && data.data && data.data.reportes) {
            // Cargar los datos del reporte en el objeto reporteDiario
            reporteDiario.registros = data.data.reportes.map(reporte => ({
                id_empleado: reporte.id_empleado,
                horas_trabajadas: reporte.horas_trabajadas,
                horas_ausentes: reporte.horas_ausentes,
                id_razon: reporte.id_razon,
                comentarios: reporte.comentarios
            }));
            return true; // Indicar que se puede mostrar la tabla
        } else {
            // Si no hay reporte, limpiar registros
            reporteDiario.registros = [];
            return true; // Indicar que se puede mostrar la tabla (aunque esté vacía)
        }
    } catch (error) {
        console.error('Error al cargar reporte por fecha:', error);
        reporteDiario.registros = [];
        return true; // En caso de error, permitir mostrar la tabla
    }
}

/**
 * Muestra la tabla de empleados con los datos del reporte
 */
function mostrarTablaEmpleados() {
    const tbody = document.getElementById('tabla-empleados-body');
    const sinEmpleados = document.getElementById('sin-empleados');
    
    if (!tbody) return;

    if (!empleadosFiltrados || empleadosFiltrados.length === 0) {
        tbody.innerHTML = '';
        if (sinEmpleados) {
            sinEmpleados.classList.remove('hidden');
            // Actualizar mensaje según si hay filtro de departamento
            const mensajeSinEmpleados = document.getElementById('mensaje-sin-empleados');
            if (mensajeSinEmpleados) {
                if (departamentoSeleccionado && departamentoSeleccionado !== '') {
                    mensajeSinEmpleados.textContent = `No hay empleados en el departamento "${departamentoSeleccionado}"`;
                } else if (empleados.length === 0) {
                    mensajeSinEmpleados.textContent = 'No hay empleados registrados en el sistema';
                } else {
                    mensajeSinEmpleados.textContent = 'No hay empleados para mostrar';
                }
            }
        }
        return;
    }

    if (sinEmpleados) {
        sinEmpleados.classList.add('hidden');
    }

    // Ordenar empleados por código de empleado (menor a mayor)
    const empleadosOrdenados = [...empleadosFiltrados].sort((a, b) => {
        const codigoA = a.codigo_empleado || '';
        const codigoB = b.codigo_empleado || '';
        
        // Si ambos están vacíos, mantener orden original
        if (!codigoA && !codigoB) return 0;
        
        // Si A está vacío, ponerlo al final
        if (!codigoA) return 1;
        
        // Si B está vacío, ponerlo al final
        if (!codigoB) return -1;
        
        // Intentar comparar como números si ambos son numéricos
        const numA = parseFloat(codigoA);
        const numB = parseFloat(codigoB);
        
        if (!isNaN(numA) && !isNaN(numB) && codigoA === numA.toString() && codigoB === numB.toString()) {
            return numA - numB;
        }
        
        // Comparar como strings (orden natural)
        return codigoA.localeCompare(codigoB, 'es', { numeric: true, sensitivity: 'base' });
    });

    // Generar filas de la tabla
    tbody.innerHTML = empleadosOrdenados.map(empleado => {
        // Buscar si hay datos guardados para este empleado
        const registro = reporteDiario.registros.find(r => r.id_empleado === empleado.id_empleado);
        
        const horasTrabajadas = registro ? registro.horas_trabajadas || '' : '';
        const horasAusentes = registro ? registro.horas_ausentes || '' : '';
        const razonId = registro ? registro.id_razon || '' : '';
        const comentarios = registro ? registro.comentarios || '' : '';
        
        // Generar opciones del select de razones
        const opcionesRazones = razonesAusentismos.map(razon => {
            const selected = razonId && parseInt(razon.id_razon) === parseInt(razonId) ? 'selected' : '';
            return `<option value="${razon.id_razon}" ${selected}>${razon.razon || ''}</option>`;
        }).join('');
        
        return `
            <tr class="empleado-fila hover:bg-gray-50" data-empleado-id="${empleado.id_empleado}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.codigo_empleado || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.nombres || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.apellidos || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${empleado.departamento || 'No especificado'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0"
                        class="horas-trabajadas w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-empleado-id="${empleado.id_empleado}"
                        value="${horasTrabajadas}"
                        placeholder="0.00"
                    >
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0"
                        class="horas-ausentes w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-empleado-id="${empleado.id_empleado}"
                        value="${horasAusentes}"
                        placeholder="0.00"
                    >
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <select 
                        class="razon-ausencia w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-empleado-id="${empleado.id_empleado}"
                    >
                        <option value="">Seleccione una razón</option>
                        ${opcionesRazones}
                    </select>
                </td>
                <td class="px-6 py-4 text-sm">
                    <textarea 
                        class="comentarios w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        rows="2"
                        data-empleado-id="${empleado.id_empleado}"
                        placeholder="Comentarios..."
                    >${comentarios}</textarea>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                        class="btn-editar-fila text-blue-600 hover:text-blue-900 mr-3" 
                        data-empleado-id="${empleado.id_empleado}"
                    >
                        Editar
                    </button>
                    <button 
                        class="btn-eliminar-fila text-red-600 hover:text-red-900" 
                        data-empleado-id="${empleado.id_empleado}"
                    >
                        Eliminar
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    // Agregar event listeners a los inputs para guardar cambios automáticamente
    agregarEventListenersInputs();
}

/**
 * Agrega event listeners a los inputs para actualizar el objeto reporteDiario
 */
function agregarEventListenersInputs() {
    // Horas trabajadas
    document.querySelectorAll('.horas-trabajadas').forEach(input => {
        input.addEventListener('change', function() {
            const empleadoId = parseInt(this.getAttribute('data-empleado-id'));
            const valor = parseFloat(this.value) || 0;
            actualizarRegistro(empleadoId, 'horas_trabajadas', valor);
        });
    });

    // Horas ausentes
    document.querySelectorAll('.horas-ausentes').forEach(input => {
        input.addEventListener('change', function() {
            const empleadoId = parseInt(this.getAttribute('data-empleado-id'));
            const valor = parseFloat(this.value) || 0;
            actualizarRegistro(empleadoId, 'horas_ausentes', valor);
        });
    });

    // Razón de ausencia
    document.querySelectorAll('.razon-ausencia').forEach(select => {
        select.addEventListener('change', function() {
            const empleadoId = parseInt(this.getAttribute('data-empleado-id'));
            const valor = this.value ? parseInt(this.value) : null;
            actualizarRegistro(empleadoId, 'id_razon', valor);
        });
    });

    // Comentarios
    document.querySelectorAll('.comentarios').forEach(textarea => {
        textarea.addEventListener('change', function() {
            const empleadoId = parseInt(this.getAttribute('data-empleado-id'));
            const valor = this.value.trim();
            actualizarRegistro(empleadoId, 'comentarios', valor);
        });
    });
}

/**
 * Actualiza un registro en el objeto reporteDiario
 */
function actualizarRegistro(empleadoId, campo, valor) {
    let registro = reporteDiario.registros.find(r => r.id_empleado === empleadoId);
    
    if (!registro) {
        registro = {
            id_empleado: empleadoId,
            horas_trabajadas: null,
            horas_ausentes: null,
            id_razon: null,
            comentarios: null
        };
        reporteDiario.registros.push(registro);
    }
    
    registro[campo] = valor;
}

/**
 * Permite editar una fila (por ahora solo marca visualmente, los campos ya son editables)
 */
function editarFila(empleadoId) {
    const fila = document.querySelector(`tr[data-empleado-id="${empleadoId}"]`);
    if (fila) {
        fila.classList.toggle('bg-yellow-50');
        
        // Scroll a la fila
        fila.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

/**
 * Elimina una fila de la tabla
 */
function eliminarFila(empleadoId) {
    if (!confirm(`¿Está seguro de que desea eliminar el registro de este empleado?\n\nEsta acción eliminará los datos ingresados para este empleado.`)) {
        return;
    }

    // Remover del array de registros
    reporteDiario.registros = reporteDiario.registros.filter(r => r.id_empleado !== empleadoId);
    
    // Recargar la tabla para limpiar los campos
    mostrarTablaEmpleados();
    
    mostrarMensajeGlobal('success', 'Registro eliminado correctamente. Recuerde guardar los cambios.');
}

/**
 * Guarda el reporte en el servidor
 */
async function guardarReporte() {
    if (!fechaSeleccionada) {
        mostrarMensajeGlobal('error', 'Por favor, seleccione una fecha antes de guardar.');
        return;
    }

    if (!empleadosFiltrados || empleadosFiltrados.length === 0) {
        mostrarMensajeGlobal('error', 'No hay empleados para guardar en el reporte.');
        return;
    }

    // Filtrar y preparar solo los registros que tienen datos
    const registrosConDatos = reporteDiario.registros.filter(registro => {
        // Asegurar que tenga id_empleado
        if (!registro.id_empleado) {
            return false;
        }
        
        // Verificar que tenga al menos un dato
        return registro.horas_trabajadas !== null && registro.horas_trabajadas !== '' ||
               registro.horas_ausentes !== null && registro.horas_ausentes !== '' ||
               registro.id_razon !== null ||
               (registro.comentarios && registro.comentarios.trim() !== '');
    });

    if (registrosConDatos.length === 0) {
        mostrarMensajeGlobal('error', 'Por favor, ingrese al menos un dato antes de guardar.');
        return;
    }

    // Preparar los datos para enviar, asegurando que todos tengan id_empleado
    const datosParaEnviar = {
        fecha: fechaSeleccionada,
        registros: registrosConDatos.map(registro => ({
            id_empleado: parseInt(registro.id_empleado),
            horas_trabajadas: registro.horas_trabajadas !== null && registro.horas_trabajadas !== '' 
                ? parseFloat(registro.horas_trabajadas) : null,
            horas_ausentes: registro.horas_ausentes !== null && registro.horas_ausentes !== '' 
                ? parseFloat(registro.horas_ausentes) : null,
            id_razon: registro.id_razon ? parseInt(registro.id_razon) : null,
            comentarios: registro.comentarios && registro.comentarios.trim() !== '' 
                ? registro.comentarios.trim() : null
        }))
    };

    try {
        mostrarLoadingBoton(true);

        const response = await fetch('/reporte-diario/guardar-masivo', fetchConfig('POST', datosParaEnviar));
        const data = await response.json();

        if (data.success) {
            mostrarMensajeGlobal('success', data.message || 'Reporte guardado exitosamente.');
            
            // Recargar los datos para reflejar los cambios
            await cargarReportePorFecha();
        } else {
            // Manejar errores de validación
            if (response.status === 422 && data.errors) {
                // Construir mensaje con todos los errores
                const mensajesError = [];
                for (const campo in data.errors) {
                    if (data.errors.hasOwnProperty(campo)) {
                        mensajesError.push(...data.errors[campo]);
                    }
                }
                const mensajeCompleto = mensajesError.length > 0 
                    ? mensajesError.join(' ') 
                    : data.message || 'Los datos proporcionados no son válidos.';
                mostrarMensajeGlobal('error', mensajeCompleto);
            } else {
                mostrarMensajeGlobal('error', data.message || 'Error al guardar el reporte.');
            }
        }
    } catch (error) {
        console.error('Error al guardar reporte:', error);
        mostrarMensajeGlobal('error', 'Error al guardar el reporte. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoadingBoton(false);
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
        
        // Ocultar después de 5 segundos (o 10 para mensajes informativos)
        const tiempo = tipo === 'info' ? 10000 : 5000;
        setTimeout(() => {
            contenedor.classList.add('hidden');
        }, tiempo);
    }
}

/**
 * Muestra/oculta el estado de carga
 */
function mostrarLoading(mostrar) {
    const tbody = document.getElementById('tabla-empleados-body');
    if (tbody && mostrar) {
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">Cargando...</td></tr>';
    }
}

/**
 * Muestra/oculta el estado de carga en el botón guardar
 */
function mostrarLoadingBoton(mostrar) {
    const btnGuardar = document.getElementById('btn-guardar-reporte');
    if (!btnGuardar) return;

    if (mostrar) {
        btnGuardar.disabled = true;
        btnGuardar.dataset.originalText = btnGuardar.innerHTML;
        btnGuardar.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';
    } else {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = btnGuardar.dataset.originalText || btnGuardar.innerHTML;
    }
}

