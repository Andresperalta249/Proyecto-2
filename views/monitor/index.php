<?php
$subtitulo = isset($subtitulo) ? $subtitulo : 'Monitorea en tiempo real los dispositivos y mascotas asociados.';
?>
<p class="subtitle text-md">
  <?= htmlspecialchars($subtitulo) ?>
</p>
<div class="container">
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-body">
            <!-- Eliminar encabezados y subtítulos, incluyendo el include del header_titulo.php -->
        </div>
    </div>
    <div class="row">
        <?php if (empty($dispositivos)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No tienes dispositivos registrados. Por favor, agrega uno para comenzar a monitorear.
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($dispositivos as $dispositivo): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <?= $dispositivo['nombre'] ?>
                        <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?> float-end">
                            <?= ucfirst($dispositivo['estado']) ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>MAC:</strong> <?= $dispositivo['mac'] ?><br>
                                <strong>ID:</strong> <?= $dispositivo['id_dispositivo'] ?><br>
                                <strong>Mascota:</strong> <?= $dispositivo['nombre_mascota'] ?><br>
                                <strong>Especie:</strong> <?= $dispositivo['especie_mascota'] ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id_dispositivo'] ?>" class="btn btn-primary">
                                    <i class="fas fa-chart-line"></i> Ver Monitor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div> 