<div class="dashboard-container">
    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="totalDispositivos">0</div>
                        <div class="kpi-label text-sm">Dispositivos Conectados</div>
                    </div>
                    <i class="fas fa-microchip kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="totalMascotas">0</div>
                        <div class="kpi-label text-sm">Mascotas Registradas</div>
                    </div>
                    <i class="fas fa-paw kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="totalAlertas">0</div>
                        <div class="kpi-label text-sm">Alertas Activas</div>
                    </div>
                    <i class="fas fa-bell kpi-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="usuariosRegistrados">0</div>
                        <div class="kpi-label text-sm">Usuarios Registrados</div>
                    </div>
                    <i class="fas fa-users kpi-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de rango de días -->
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center gap-2">
            <label for="rangoDias" class="form-label mb-0">Rango de días:</label>
            <select id="rangoDias" class="form-select form-select-sm" style="width: auto;">
                <option value="7">Últimos 7 días</option>
                <option value="15">Últimos 15 días</option>
                <option value="30">Últimos 30 días</option>
            </select>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="chart-container">
                <div class="chart-title text-md fw-bold">Alertas por día</div>
                <canvas id="alertasChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container">
                <div class="chart-title text-md fw-bold">Distribución de especies</div>
                <canvas id="especiesChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container">
                <div class="chart-title text-md fw-bold">Registros por día</div>
                <canvas id="usuariosChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity List -->
    <div class="row">
        <div class="col-12">
            <div class="activity-list">
                <h5 class="mb-3 fw-semibold">Actividad Reciente</h5>
                <div id="activityList">
                    <!-- Activity items will be dynamically added here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #004AAD;
        --warning-color: #FFC107;
        --light-gray: #f8f9fa;
    }
    .dashboard-container {
        /* height: 100vh; */
        padding: 0.5rem 0.5rem 1rem 0.5rem;
        width: 100%;
        box-sizing: border-box;
    }
    .kpi-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        height: 100%;
        transition: transform 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
    }
    .kpi-icon {
        font-size: 2rem;
        color: var(--primary-color);
    }
    .chart-title {
        text-align: center;
        margin-bottom: 0.1rem;
        color: #004AAD;
        line-height: 1.1;
    }
    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 0.2rem 0.2rem 0 0.2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        height: 250px;
        max-height: 260px;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: stretch;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }
    .chart-container canvas {
        flex: 1 1 0;
        min-height: 0;
        height: 100% !important;
        max-height: 100% !important;
        width: 100% !important;
        max-width: 100% !important;
        display: block;
        margin: 0 !important;
        padding: 0 !important;
    }
    @media (max-width: 900px) {
        .chart-container {
            height: 160px;
            min-height: 100px;
            max-height: 180px;
            padding: 0.1rem 0.1rem 0 0.1rem;
        }
    }
    .activity-list {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        height: 100%;
        overflow-y: auto;
    }
    .activity-item {
        padding: 0.75rem;
        border-bottom: 1px solid #eee;
    }
    .activity-item:last-child {
        border-bottom: none;
    }
    .alert-badge {
        background-color: var(--warning-color);
        color: #000;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }
    .bateria-label {
        font-weight: bold;
        color: #004AAD;
    }
</style>

<script>
// Detectar la base del proyecto automáticamente
const BASE_URL = window.location.pathname.split('/dashboard')[0] + '/dashboard/';

// Variables globales para las gráficas
let alertasChart, especiesChart, usuariosChart;

// Inicializar las gráficas
function initCharts() {
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 8,
                    font: {
                        size: 10
                    }
                }
            }
        }
    };

    // Inicializar gráfica de alertas
    const alertasCtx = document.getElementById('alertasChart').getContext('2d');
    alertasChart = new Chart(alertasCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Alertas',
                data: [],
                borderColor: '#004AAD',
                backgroundColor: 'rgba(0, 74, 173, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

    // Inicializar gráfica de especies
    const especiesCtx = document.getElementById('especiesChart').getContext('2d');
    especiesChart = new Chart(especiesCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#004AAD',
                    '#FFC107',
                    '#28a745',
                    '#dc3545',
                    '#17a2b8'
                ]
            }]
        },
        options: {
            ...commonOptions,
            cutout: '60%',
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value}`;
                        }
                    }
                }
            }
        }
    });

    // Inicializar gráfica de usuarios
    const usuariosCtx = document.getElementById('usuariosChart').getContext('2d');
    usuariosChart = new Chart(usuariosCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Usuarios',
                data: [],
                borderColor: '#004AAD',
                backgroundColor: 'rgba(0, 74, 173, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Mascotas',
                data: [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });
}

// Función para manejar errores de fetch
async function handleFetchError(error) {
    console.error('Error en la petición:', error);
    
    // Remover mensajes de error anteriores
    const existingAlerts = document.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
    
    // Mostrar mensaje de error al usuario
    const errorMessage = document.createElement('div');
    errorMessage.className = 'alert alert-danger alert-dismissible fade show';
    errorMessage.innerHTML = `
        <strong>Error!</strong> ${error.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.querySelector('.dashboard-container').prepend(errorMessage);
    
    // Actualizar valores por defecto
    document.getElementById('totalDispositivos').textContent = '0/0';
    document.getElementById('totalMascotas').textContent = '0';
    document.getElementById('totalAlertas').textContent = '0/0';
    document.getElementById('usuariosRegistrados').textContent = '0';
}

// Función para actualizar los KPI
async function updateKPIs() {
    try {
        const response = await fetch(BASE_URL + 'getKPIData');
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Error al obtener datos KPI');
        }
        
        const data = result.data;
        if (!data) {
            throw new Error('No se recibieron datos del servidor');
        }

        // Validar y actualizar cada KPI
        const dispositivosConectados = data.dispositivos?.conectados ?? 0;
        const dispositivosTotal = data.dispositivos?.total ?? 0;
        const mascotasTotal = data.mascotas ?? 0;
        const alertasActivas = data.alertas?.activas ?? 0;
        const alertasResueltas = data.alertas?.resueltas ?? 0;
        const usuariosRegistrados = data.usuarios_registrados ?? 0;

        document.getElementById('totalDispositivos').textContent = 
            `${dispositivosConectados}/${dispositivosTotal}`;
        document.getElementById('totalMascotas').textContent = mascotasTotal;
        document.getElementById('totalAlertas').textContent = 
            `${alertasActivas}/${alertasActivas + alertasResueltas}`;
        document.getElementById('usuariosRegistrados').textContent = usuariosRegistrados;
    } catch (error) {
        console.error('Error en updateKPIs:', error);
        handleFetchError(error);
    }
}

// Obtener el rango de días seleccionado
function getDiasSeleccionados() {
    return parseInt(document.getElementById('rangoDias').value, 10);
}

// Función para obtener el rango de fechas mostrado
function getRangoFechas(data) {
    if (!data || data.length === 0) return '';
    const desde = data[0]?.fecha || '';
    const hasta = data[data.length - 1]?.fecha || '';
    if (!desde || !hasta) return '';
    return `Datos del ${formatearFecha(desde)} al ${formatearFecha(hasta)}`;
}

// Función para formatear fecha (solo día)
function formatearFecha(fecha) {
    if (!fecha) return '';
    const d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.getDate(); // Solo retorna el día
}

// Función para actualizar la gráfica de alertas
async function updateAlertasChart() {
    if (!alertasChart) {
        console.error('Gráfica de alertas no inicializada');
        return;
    }

    try {
        const dias = getDiasSeleccionados();
        const response = await fetch(`${BASE_URL}getAlertasPorDia?dias=${dias}`);
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Error al obtener datos de alertas');
        
        const data = result.data;
        if (!data || !Array.isArray(data)) throw new Error('Datos de alertas inválidos');

        const labels = data.map(item => formatearFecha(item.fecha));
        const values = data.map(item => item.total);

        alertasChart.data.labels = labels;
        alertasChart.data.datasets[0].data = values;
        alertasChart.update();
    } catch (error) {
        console.error('Error en updateAlertasChart:', error);
        handleFetchError(error);
    }
}

// Función para actualizar la gráfica de especies
async function updateEspeciesChart() {
    if (!especiesChart) {
        console.error('Gráfica de especies no inicializada');
        return;
    }

    try {
        const response = await fetch(BASE_URL + 'getDistribucionEspecies');
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Error al obtener distribución de especies');
        
        const data = result.data;
        if (!data || !Array.isArray(data)) throw new Error('Datos de especies inválidos');

        const labels = data.map(item => `${item.especie} (${item.porcentaje}%)`);
        const values = data.map(item => item.total);

        especiesChart.data.labels = labels;
        especiesChart.data.datasets[0].data = values;
        especiesChart.update();
    } catch (error) {
        console.error('Error en updateEspeciesChart:', error);
        handleFetchError(error);
    }
}

// Función para actualizar la gráfica de usuarios
async function updateUsuariosChart() {
    if (!usuariosChart) {
        console.error('Gráfica de usuarios no inicializada');
        return;
    }

    try {
        const dias = getDiasSeleccionados();
        const response = await fetch(`${BASE_URL}getHistorialUsuarios?dias=${dias}`);
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Error al obtener historial de usuarios');
        
        const data = result.data;
        if (!data || !Array.isArray(data)) throw new Error('Datos de usuarios inválidos');

        const labels = data.map(item => formatearFecha(item.fecha));
        const usuariosData = data.map(item => item.usuarios);
        const mascotasData = data.map(item => item.mascotas);

        usuariosChart.data.labels = labels;
        usuariosChart.data.datasets[0].data = usuariosData;
        usuariosChart.data.datasets[1].data = mascotasData;
        usuariosChart.update();
    } catch (error) {
        console.error('Error en updateUsuariosChart:', error);
        handleFetchError(error);
    }
}

// Función para actualizar la lista de actividades
async function updateActivityList() {
    try {
        const response = await fetch(BASE_URL + 'getActividadReciente');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error);
        }
        
        const activities = result.data;
        const activityList = document.getElementById('activityList');
        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="alert-badge me-2">${activity.tipo}</span>
                        ${activity.mensaje}
                    </div>
                    <small class="text-muted">${activity.tiempo}</small>
                </div>
            </div>
        `).join('');
    } catch (error) {
        handleFetchError(error);
    }
}

// Función para actualizar todos los datos
async function updateAllData() {
    try {
        await Promise.all([
            updateKPIs(),
            updateAlertasChart(),
            updateEspeciesChart(),
            updateUsuariosChart(),
            updateActivityList()
        ]);
    } catch (error) {
        handleFetchError(error);
    }
}

// Inicializar las gráficas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    // Actualización inicial
    updateAllData();
    // Actualización periódica cada 30 segundos
    setInterval(updateAllData, 30000);
});

// Event listener para el selector de rango de días
document.getElementById('rangoDias').addEventListener('change', () => {
    updateAlertasChart();
    updateUsuariosChart();
});
</script>

<!-- Cargar Chart.js antes de nuestro código -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Cargar jQuery antes de cualquier plugin o JS que lo requiera -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Si usas Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Si usas DataTables y plugins -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>