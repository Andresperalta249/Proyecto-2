<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Monitor - <?= $dispositivo['nombre'] ?></h1>
        <a href="<?= BASE_URL ?>monitor" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <!-- Información del Dispositivo -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información del Dispositivo</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Tipo:</strong> <?= $dispositivo['tipo'] ?><br>
                        <strong>ID:</strong> <?= $dispositivo['identificador'] ?><br>
                        <strong>Estado:</strong> 
                        <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?>">
                            <?= ucfirst($dispositivo['estado']) ?>
                        </span>
                    </p>
                    <hr>
                    <h6>Mascota Asociada</h6>
                    <p class="mb-0">
                        <strong>Nombre:</strong> <?= $mascota['nombre'] ?><br>
                        <strong>Especie:</strong> <?= $mascota['especie'] ?><br>
                        <strong>Raza:</strong> <?= $mascota['raza'] ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estadísticas (Últimas 24 horas)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Temperatura</h6>
                                    <p class="mb-0">
                                        Promedio: <?= number_format($estadisticas['temp_promedio'], 1) ?>°C<br>
                                        Máxima: <?= number_format($estadisticas['temp_maxima'], 1) ?>°C<br>
                                        Mínima: <?= number_format($estadisticas['temp_minima'], 1) ?>°C
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Humedad</h6>
                                    <p class="mb-0">
                                        Promedio: <?= number_format($estadisticas['hum_promedio'], 1) ?>%<br>
                                        Máxima: <?= number_format($estadisticas['hum_maxima'], 1) ?>%<br>
                                        Mínima: <?= number_format($estadisticas['hum_minima'], 1) ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Actividad</h6>
                                    <p class="mb-0">
                                        Promedio: <?= number_format($estadisticas['act_promedio'], 1) ?><br>
                                        Máxima: <?= number_format($estadisticas['act_maxima'], 1) ?><br>
                                        Mínima: <?= number_format($estadisticas['act_minima'], 1) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Gráficos en Tiempo Real</h5>
                </div>
                <div class="card-body">
                    <canvas id="monitorChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Datos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Últimos Datos Registrados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Temperatura</th>
                                    <th>Humedad</th>
                                    <th>Actividad</th>
                                    <th>Batería</th>
                                </tr>
                            </thead>
                            <tbody id="ultimosDatos">
                                <?php foreach ($ultimosDatos as $dato): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i:s', strtotime($dato['fecha'])) ?></td>
                                    <td><?= number_format($dato['temperatura'], 1) ?>°C</td>
                                    <td><?= number_format($dato['humedad'], 1) ?>%</td>
                                    <td><?= number_format($dato['actividad'], 1) ?></td>
                                    <td><?= number_format($dato['bateria'], 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chart;
let ultimaActualizacion = new Date();

// Inicializar gráfico
function initChart() {
    const ctx = document.getElementById('monitorChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Temperatura (°C)',
                    data: [],
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                },
                {
                    label: 'Humedad (%)',
                    data: [],
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                },
                {
                    label: 'Actividad',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Actualizar datos
function actualizarDatos() {
    fetch('<?= BASE_URL ?>monitor/getData/<?= $dispositivo['id'] ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar tabla
                const tbody = document.getElementById('ultimosDatos');
                tbody.innerHTML = '';
                data.data.ultimosDatos.forEach(dato => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${new Date(dato.fecha).toLocaleString()}</td>
                            <td>${Number(dato.temperatura).toFixed(1)}°C</td>
                            <td>${Number(dato.humedad).toFixed(1)}%</td>
                            <td>${Number(dato.actividad).toFixed(1)}</td>
                            <td>${Number(dato.bateria).toFixed(1)}%</td>
                        </tr>
                    `;
                });

                // Actualizar gráfico
                const labels = data.data.ultimosDatos.map(d => new Date(d.fecha).toLocaleTimeString());
                const tempData = data.data.ultimosDatos.map(d => d.temperatura);
                const humData = data.data.ultimosDatos.map(d => d.humedad);
                const actData = data.data.ultimosDatos.map(d => d.actividad);

                chart.data.labels = labels;
                chart.data.datasets[0].data = tempData;
                chart.data.datasets[1].data = humData;
                chart.data.datasets[2].data = actData;
                chart.update();

                ultimaActualizacion = new Date();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    actualizarDatos();
    // Actualizar cada 30 segundos
    setInterval(actualizarDatos, 30000);
});
</script> 