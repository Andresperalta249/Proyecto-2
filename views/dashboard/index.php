<?php
// Asegurarse de que todas las variables necesarias estén definidas
$stats = $stats ?? [
    'mascotas' => 0,
    'dispositivos' => 0,
    'dispositivos_activos' => 0,
    'alertas' => [
        'total' => 0,
        'no_leidas' => 0,
        'alertas_altas' => 0
    ]
];
$ultimasAlertas = $ultimasAlertas ?? [];
$dispositivosActivos = $dispositivosActivos ?? [];
$actividadReciente = $actividadReciente ?? [];
?>

<!-- Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary">
            <div class="card-body d-flex flex-column align-items-start">
                <div class="d-flex align-items-center w-100">
                    <div>
                        <h5 class="card-title mb-3">
                            <i class="fas fa-paw me-2"></i>Mascotas
                        </h5>
                        <h2 class="mb-1 display-6"><?= $stats['mascotas'] ?></h2>
                        <small>registradas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success">
            <div class="card-body d-flex flex-column align-items-start">
                <div class="d-flex align-items-center w-100">
                    <div>
                        <h5 class="card-title mb-3">
                            <i class="fas fa-microchip me-2"></i>Dispositivos
                        </h5>
                        <h2 class="mb-1 display-6"><?= $stats['dispositivos'] ?></h2>
                        <small><?= $stats['dispositivos_activos'] ?> activos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning">
            <div class="card-body d-flex flex-column align-items-start">
                <div class="d-flex align-items-center w-100">
                    <div>
                        <h5 class="card-title mb-3">
                            <i class="fas fa-bell me-2"></i>Alertas
                        </h5>
                        <h2 class="mb-1 display-6"><?= $stats['alertas']['total'] ?></h2>
                        <small><?= $stats['alertas']['no_leidas'] ?> no leídas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info">
            <div class="card-body d-flex flex-column align-items-start">
                <div class="d-flex align-items-center w-100">
                    <div>
                        <h5 class="card-title mb-3">
                            <i class="fas fa-history me-2"></i>Actividad
                        </h5>
                        <h2 class="mb-1 display-6"><?= count($actividadReciente) ?></h2>
                        <small>últimas acciones</small>
                    </div>
                </div>
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
                                <h6 class="mb-1"><?= htmlspecialchars($alerta['titulo']) ?></h6>
                                <small class="text-<?= $alerta['leida'] ? 'muted' : 'danger' ?>">
                                    <?= $alerta['leida'] ? 'Leída' : 'No leída' ?>
                                </small>
                            </div>
                            <p class="mb-1"><?= htmlspecialchars($alerta['mensaje']) ?></p>
                            <small class="text-muted">
                                <?= htmlspecialchars($alerta['mascota_nombre']) ?> - 
                                <?= date('d/m/Y H:i', strtotime($alerta['fecha_creacion'])) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?= APP_URL ?>/alertas" class="btn btn-primary">Ver todas las alertas</a>
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
                                <h6 class="mb-1"><?= htmlspecialchars($dispositivo['nombre']) ?></h6>
                                <span class="badge bg-success">Activo</span>
                            </div>
                            <p class="mb-1">
                                <small class="text-muted">
                                    <?= htmlspecialchars($dispositivo['tipo']) ?> - 
                                    <?= htmlspecialchars($dispositivo['mascota_nombre'] ?? 'Sin mascota asignada') ?>
                                </small>
                            </p>
                            <a href="<?= APP_URL ?>/monitor/device/<?= $dispositivo['id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-chart-line"></i> Ver Monitor
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?= APP_URL ?>/dispositivos" class="btn btn-primary">Ver todos los dispositivos</a>
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <?php if (empty($actividadReciente)): ?>
                    <p class="text-muted">No hay actividad reciente</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($actividadReciente as $actividad): ?>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($actividad['descripcion']) ?></h6>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($actividad['fecha'])) ?>
                                </small>
                            </div>
                            <small class="text-muted">
                                <?= htmlspecialchars($actividad['tipo']) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar estadísticas cada 5 minutos
setInterval(() => {
    fetch('<?= APP_URL ?>/dashboard/getStats')
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