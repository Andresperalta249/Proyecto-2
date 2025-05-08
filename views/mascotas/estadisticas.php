<?php require_once 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Estadísticas de Mascotas</h1>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Mascotas</h5>
                    <h2 class="card-text"><?= $estadisticas['total_mascotas'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Especies Diferentes</h5>
                    <h2 class="card-text"><?= $estadisticas['total_especies'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Dispositivos Activos</h5>
                    <h2 class="card-text"><?= $estadisticas['total_dispositivos'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Registros Médicos</h5>
                    <h2 class="card-text"><?= $estadisticas['total_registros_medicos'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución por Edad -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Distribución por Edad</h5>
                </div>
                <div class="card-body">
                    <canvas id="edadChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Distribución por Especie</h5>
                </div>
                <div class="card-body">
                    <canvas id="especieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de Mascotas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Estado de Mascotas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mascota</th>
                                    <th>Especie</th>
                                    <th>Estado</th>
                                    <th>Dispositivos</th>
                                    <th>Alertas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mascotas_estado as $mascota): ?>
                                <tr>
                                    <td><?= htmlspecialchars($mascota['nombre']) ?></td>
                                    <td><?= htmlspecialchars($mascota['especie']) ?></td>
                                    <td>
                                        <?php if ($mascota['estado'] === 'sin_dispositivo'): ?>
                                            <span class="badge bg-warning">Sin Dispositivo</span>
                                        <?php elseif ($mascota['estado'] === 'con_alerta'): ?>
                                            <span class="badge bg-danger">Con Alerta</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $mascota['total_dispositivos'] ?? 0 ?></td>
                                    <td><?= $mascota['total_alertas'] ?? 0 ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximas Vacunas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Próximas Vacunas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mascota</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Días Restantes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximas_vacunas as $vacuna): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vacuna['nombre']) ?></td>
                                    <td><?= htmlspecialchars($vacuna['tipo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($vacuna['fecha'])) ?></td>
                                    <td>
                                        <?php
                                        $dias = ceil((strtotime($vacuna['fecha']) - time()) / (60 * 60 * 24));
                                        if ($dias < 0) {
                                            echo '<span class="badge bg-danger">Vencida</span>';
                                        } elseif ($dias <= 7) {
                                            echo '<span class="badge bg-warning">' . $dias . ' días</span>';
                                        } else {
                                            echo '<span class="badge bg-info">' . $dias . ' días</span>';
                                        }
                                        ?>
                                    </td>
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
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de distribución por edad
    const edadCtx = document.getElementById('edadChart').getContext('2d');
    new Chart(edadCtx, {
        type: 'bar',
        data: {
            labels: ['0-1 año', '1-3 años', '3-5 años', '5-10 años', '10+ años'],
            datasets: [{
                label: 'Cantidad de Mascotas',
                data: [
                    <?= $estadisticas['edad_0_1'] ?? 0 ?>,
                    <?= $estadisticas['edad_1_3'] ?? 0 ?>,
                    <?= $estadisticas['edad_3_5'] ?? 0 ?>,
                    <?= $estadisticas['edad_5_10'] ?? 0 ?>,
                    <?= $estadisticas['edad_10_plus'] ?? 0 ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(255, 99, 132, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Gráfico de distribución por especie
    const especieCtx = document.getElementById('especieChart').getContext('2d');
    new Chart(especieCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($estadisticas['distribucion_especies'], 'especie')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($estadisticas['distribucion_especies'], 'total')) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?> 