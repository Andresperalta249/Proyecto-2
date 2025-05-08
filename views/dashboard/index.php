<div class="container">
    <h1 class="mb-4">Dashboard</h1>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Mascotas</h5>
                    <h2 class="mb-0"><?= $stats['mascotas'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Dispositivos</h5>
                    <h2 class="mb-0"><?= $stats['dispositivos'] ?></h2>
                    <small><?= $stats['dispositivos_activos'] ?> activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Alertas</h5>
                    <h2 class="mb-0"><?= $stats['alertas']['total'] ?></h2>
                    <small><?= $stats['alertas']['no_leidas'] ?> no leídas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Actividad</h5>
                    <h2 class="mb-0"><?= count($actividadReciente) ?></h2>
                    <small>últimas acciones</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Últimas Alertas -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Últimas Alertas</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ultimasAlertas)): ?>
                        <p class="text-muted">No hay alertas nuevas</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($ultimasAlertas as $alerta): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-<?= $alerta['tipo'] === 'error' ? 'danger' : ($alerta['tipo'] === 'advertencia' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($alerta['tipo']) ?>
                                        </span>
                                        <?= $alerta['mensaje'] ?>
                                    </h6>
                                    <small><?= date('d/m/Y H:i', strtotime($alerta['fecha'])) ?></small>
                                </div>
                                <small class="text-muted">
                                    <?= $alerta['dispositivo_nombre'] ?? 'N/A' ?> - 
                                    <?= $alerta['mascota_nombre'] ?? 'N/A' ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>alertas" class="btn btn-primary btn-sm">Ver todas las alertas</a>
                </div>
            </div>
        </div>

        <!-- Dispositivos Activos -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dispositivos Activos</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($dispositivosActivos)): ?>
                        <p class="text-muted">No hay dispositivos activos</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($dispositivosActivos as $dispositivo): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $dispositivo['nombre'] ?></h6>
                                    <span class="badge bg-success">Activo</span>
                                </div>
                                <p class="mb-1">
                                    <small class="text-muted">
                                        <?= $dispositivo['tipo'] ?> - 
                                        <?= $dispositivo['mascota_nombre'] ?? 'Sin mascota asignada' ?>
                                    </small>
                                </p>
                                <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-chart-line"></i> Ver Monitor
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>dispositivos" class="btn btn-primary btn-sm">Ver todos los dispositivos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($actividadReciente)): ?>
                        <p class="text-muted">No hay actividad reciente</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($actividadReciente as $actividad): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($actividad['fecha'])) ?></td>
                                        <td><?= $actividad['usuario_nombre'] ?></td>
                                        <td><?= $actividad['accion'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar estadísticas cada 5 minutos
setInterval(() => {
    fetch('<?= BASE_URL ?>dashboard/getStats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contadores
                document.querySelector('.bg-primary h2').textContent = data.data.mascotas;
                document.querySelector('.bg-success h2').textContent = data.data.dispositivos;
                document.querySelector('.bg-success small').textContent = data.data.dispositivos_activos + ' activos';
                document.querySelector('.bg-warning h2').textContent = data.data.alertas.total;
                document.querySelector('.bg-warning small').textContent = data.data.alertas.no_leidas + ' no leídas';
            }
        })
        .catch(error => console.error('Error:', error));
}, 300000);
</script> 