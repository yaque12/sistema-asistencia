// Panel de Control de Asistencia - Funcionalidad Frontend
// Este archivo maneja toda la lógica del panel de control de asistencia

// Variables globales
let graficaSemanal = null;
let estadisticasActuales = null;

// Obtener token CSRF del meta tag
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

// Inicializar el panel cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarPanelAsistencia();
});

/**
 * Inicializar el panel de asistencia
 */
function inicializarPanelAsistencia() {
    const btnRegistrarAsistencia = document.getElementById('btnRegistrarAsistencia');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const btnCerrarModal2 = document.getElementById('btnCerrarModal2');
    const modal = document.getElementById('modalPanelAsistencia');
    
    // Si no existe el botón, no hacer nada (no estamos en la página de bienvenida)
    if (!btnRegistrarAsistencia) {
        return;
    }
    
    // Event listener para abrir el modal
    btnRegistrarAsistencia.addEventListener('click', function() {
        abrirModal();
    });
    
    // Event listeners para cerrar el modal
    if (btnCerrarModal) {
        btnCerrarModal.addEventListener('click', cerrarModal);
    }
    
    if (btnCerrarModal2) {
        btnCerrarModal2.addEventListener('click', cerrarModal);
    }
    
    // Cerrar modal al hacer clic fuera de él
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });
    }
    
    // Cargar estadísticas iniciales para el badge
    cargarEstadisticasParaBadge();
}

/**
 * Abrir el modal y cargar datos
 */
function abrirModal() {
    const modal = document.getElementById('modalPanelAsistencia');
    if (!modal) {
        return;
    }
    
    // Mostrar el modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Cargar estadísticas
    cargarEstadisticas();
}

/**
 * Cerrar el modal
 */
function cerrarModal() {
    const modal = document.getElementById('modalPanelAsistencia');
    if (!modal) {
        return;
    }
    
    // Ocultar el modal
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/**
 * Cargar estadísticas desde la API
 */
async function cargarEstadisticas() {
    try {
        // Mostrar estado de carga
        mostrarEstadoCarga();
        
        // Hacer petición a la API
        const response = await fetch('/api/asistencia/estadisticas', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        // Verificar si la respuesta es HTML (redirección a login)
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/html')) {
            throw new Error('No estás autenticado. Por favor, recarga la página.');
        }
        
        if (!response.ok) {
            // Intentar obtener el mensaje de error del JSON
            let errorMessage = 'Error al cargar estadísticas';
            try {
                const errorData = await response.json();
                if (errorData.message) {
                    errorMessage = errorData.message;
                }
            } catch (e) {
                errorMessage = `Error ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorMessage);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            estadisticasActuales = data.data;
            actualizarIndicadores(data.data);
            renderizarGraficaSemanal(data.data.semana);
        } else {
            const mensaje = data.message || 'No se pudieron cargar las estadísticas';
            mostrarError(mensaje);
        }
        
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        const mensajeError = error.message || 'Error al cargar las estadísticas. Por favor, intente nuevamente.';
        mostrarError(mensajeError);
    }
}

/**
 * Cargar estadísticas para el badge del botón
 */
async function cargarEstadisticasParaBadge() {
    try {
        const response = await fetch('/api/asistencia/estadisticas', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            return;
        }
        
        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return;
        }
        
        const data = await response.json();
        
        if (data.success && data.data && data.data.dia_actual) {
            actualizarBadge(data.data.dia_actual);
        }
        
    } catch (error) {
        console.error('Error al cargar estadísticas para badge:', error);
        // No mostrar error al usuario, solo no mostrar el badge
    }
}

/**
 * Actualizar el badge del botón
 */
function actualizarBadge(diaActual) {
    const badge = document.getElementById('badgeAsistencia');
    const porcentajeBadge = document.getElementById('porcentajeBadge');
    
    if (!badge || !porcentajeBadge) {
        return;
    }
    
    const porcentaje = diaActual.porcentaje || 0;
    
    // Mostrar el badge
    badge.classList.remove('hidden');
    porcentajeBadge.textContent = Math.round(porcentaje) + '%';
    
    // Cambiar color según el porcentaje
    badge.classList.remove('bg-green-500', 'bg-yellow-500', 'bg-red-500');
    if (porcentaje >= 80) {
        badge.classList.add('bg-green-500');
    } else if (porcentaje >= 50) {
        badge.classList.add('bg-yellow-500');
    } else {
        badge.classList.add('bg-red-500');
    }
}

/**
 * Actualizar los indicadores del día actual
 */
function actualizarIndicadores(data) {
    const diaActual = data.dia_actual;
    
    // Actualizar fecha
    const fechaTexto = document.getElementById('fechaActualTexto');
    if (fechaTexto && diaActual.fecha) {
        // Parsear la fecha correctamente (formato Y-m-d)
        // La fecha viene del servidor en formato Y-m-d (ej: 2026-01-06)
        // IMPORTANTE: No usar new Date(string) porque puede interpretar como UTC y cambiar el día
        const partesFecha = diaActual.fecha.split('-');
        if (partesFecha.length === 3) {
            const año = parseInt(partesFecha[0], 10);
            const mes = parseInt(partesFecha[1], 10) - 1; // Los meses en JS van de 0-11
            const dia = parseInt(partesFecha[2], 10);
            
            // Crear la fecha usando el constructor local (no UTC) para mantener el día correcto
            // new Date(año, mes, dia) crea la fecha en la zona horaria local del navegador
            const fecha = new Date(año, mes, dia);
            
            // Log para depuración
            console.log('Fecha recibida del servidor:', diaActual.fecha);
            console.log('Fecha parseada:', fecha);
            console.log('Día de la semana (número):', fecha.getDay()); // 0=domingo, 1=lunes, etc.
            
            // Verificar que la fecha sea válida
            if (!isNaN(fecha.getTime())) {
                // No usar timeZone en las opciones porque la fecha ya está en formato local
                // El servidor ya calculó la fecha correcta según la zona horaria de El Salvador
                const opciones = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric'
                };
                const fechaFormateada = fecha.toLocaleDateString('es-ES', opciones);
                console.log('Fecha formateada:', fechaFormateada);
                fechaTexto.textContent = fechaFormateada;
            } else {
                fechaTexto.textContent = diaActual.fecha;
            }
        } else {
            fechaTexto.textContent = diaActual.fecha;
        }
    }
    
    // Actualizar porcentaje
    const porcentajeDiaActual = document.getElementById('porcentajeDiaActual');
    if (porcentajeDiaActual) {
        porcentajeDiaActual.textContent = diaActual.porcentaje.toFixed(2) + '%';
    }
    
    // Actualizar detalle
    const personasConHoras = document.getElementById('personasConHoras');
    const totalEmpleados = document.getElementById('totalEmpleados');
    if (personasConHoras) {
        personasConHoras.textContent = diaActual.personas_con_horas || 0;
    }
    if (totalEmpleados) {
        totalEmpleados.textContent = diaActual.total_empleados || 0;
    }
    
    // Actualizar ícono según el porcentaje
    const iconoEstado = document.getElementById('iconoEstado');
    if (iconoEstado) {
        const porcentaje = diaActual.porcentaje || 0;
        if (porcentaje >= 80) {
            iconoEstado.textContent = '✅';
        } else if (porcentaje >= 50) {
            iconoEstado.textContent = '⚠️';
        } else {
            iconoEstado.textContent = '❌';
        }
    }
}

/**
 * Renderizar la gráfica semanal con Chart.js
 */
function renderizarGraficaSemanal(datosSemana) {
    const canvas = document.getElementById('graficaSemanal');
    if (!canvas) {
        return;
    }
    
    // Destruir gráfica anterior si existe
    if (graficaSemanal) {
        graficaSemanal.destroy();
    }
    
    // Preparar datos para la gráfica
    const labels = datosSemana.map(dia => dia.dia_semana_corto);
    const porcentajes = datosSemana.map(dia => dia.porcentaje || 0);
    
    // Determinar colores según el porcentaje
    const colores = porcentajes.map(porcentaje => {
        if (porcentaje >= 80) {
            return 'rgba(34, 197, 94, 0.8)'; // Verde
        } else if (porcentaje >= 50) {
            return 'rgba(234, 179, 8, 0.8)'; // Amarillo
        } else {
            return 'rgba(239, 68, 68, 0.8)'; // Rojo
        }
    });
    
    // Detectar si está en modo oscuro
    const esModoOscuro = document.documentElement.classList.contains('dark');
    const colorTexto = esModoOscuro ? 'rgba(237, 242, 247, 0.9)' : 'rgba(26, 32, 44, 0.9)';
    const colorGrid = esModoOscuro ? 'rgba(237, 242, 247, 0.1)' : 'rgba(26, 32, 44, 0.1)';
    
    // Crear la gráfica
    const ctx = canvas.getContext('2d');
    graficaSemanal = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Porcentaje de Asistencia',
                data: porcentajes,
                backgroundColor: colores,
                borderColor: colores.map(color => color.replace('0.8', '1')),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: esModoOscuro ? 'rgba(26, 32, 44, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                    titleColor: colorTexto,
                    bodyColor: colorTexto,
                    borderColor: colorGrid,
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const dia = datosSemana[context.dataIndex];
                            return `${dia.personas_con_horas} / ${dia.total_empleados} empleados (${dia.porcentaje.toFixed(2)}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        color: colorTexto,
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: colorGrid,
                    },
                    title: {
                        display: true,
                        text: 'Porcentaje de Asistencia',
                        color: colorTexto,
                    }
                },
                x: {
                    ticks: {
                        color: colorTexto,
                    },
                    grid: {
                        color: colorGrid,
                    },
                    title: {
                        display: true,
                        text: 'Días de la Semana',
                        color: colorTexto,
                    }
                }
            }
        }
    });
}

/**
 * Mostrar estado de carga
 */
function mostrarEstadoCarga() {
    const porcentajeDiaActual = document.getElementById('porcentajeDiaActual');
    const fechaActualTexto = document.getElementById('fechaActualTexto');
    
    if (porcentajeDiaActual) {
        porcentajeDiaActual.textContent = 'Cargando...';
    }
    if (fechaActualTexto) {
        fechaActualTexto.textContent = 'Cargando...';
    }
}

/**
 * Mostrar mensaje de error
 */
function mostrarError(mensaje) {
    const porcentajeDiaActual = document.getElementById('porcentajeDiaActual');
    const fechaActualTexto = document.getElementById('fechaActualTexto');
    
    if (porcentajeDiaActual) {
        porcentajeDiaActual.textContent = 'Error';
    }
    if (fechaActualTexto) {
        fechaActualTexto.textContent = mensaje;
    }
}

