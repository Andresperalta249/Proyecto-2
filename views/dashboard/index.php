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
<div class="container-xxl px-3 py-2" style="max-width: 100vw; margin: 0;">
    <div class="row g-4 mb-4 dashboard-row">
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

<style>
body {
    overflow-x: hidden;
    background: #f8f9fa;
}
.container-xxl {
    width: 100%;
    max-width: 100vw;
    margin: 0;
    padding-left: 0;
    padding-right: 0;
}
.dashboard-row {
    flex-wrap: wrap;
    margin-left: 0;
    margin-right: 0;
}
.card.stat-card {
    min-width: 0;
    word-break: break-word;
    padding: 0.3rem 0.5rem;
    border-radius: 0.7rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 0.5rem;
}
.card-body {
    padding: 0.7rem 0.5rem 0.5rem 0.5rem;
}
.card-title {
    font-size: 1rem;
}
.display-6 {
    font-size: 1.5rem;
}
.btn-info {
    font-size: 0.95rem;
    padding: 0.25rem 0.7rem;
    border-radius: 0.5rem;
}
.row {
    margin-left: 0;
    margin-right: 0;
}
@media (max-width: 991.98px) {
    .container-xxl {
        max-width: 100vw;
        padding-left: 0;
        padding-right: 0;
    }
    .card.stat-card {
        margin-bottom: 0.5rem;
    }
    .display-6 {
        font-size: 1.1rem;
    }
}
</style> 