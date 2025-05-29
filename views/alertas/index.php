<?php
?>
<div class="container-fluid">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Alertas</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConfiguracionAlertas">
            <i class="fas fa-cog"></i> Configurar Alertas
        </button>
    </div>

    <!-- Modal Configuración de Alertas -->
    <div class="modal fade" id="modalConfiguracionAlertas" tabindex="-1" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configuración General de Alertas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/proyecto-2/configuracion-alerta/actualizar-general" method="POST" id="formConfiguracionAlertas">
                    <div class="modal-body">
                        <!-- Temperatura -->
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">Temperatura</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Valor Mínimo (°C)</label>
                                            <input type="number" class="form-control" name="temperatura[min]" 
                                                   value="<?= $configuraciones['temperatura']['min'] ?? 35.5 ?>" step="0.1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Valor Máximo (°C)</label>
                                            <input type="number" class="form-control" name="temperatura[max]" 
                                                   value="<?= $configuraciones['temperatura']['max'] ?? 40.0 ?>" step="0.1" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prioridad</label>
                                    <select class="form-select" name="temperatura[prioridad]" required>
                                        <option value="baja" <?= ($configuraciones['temperatura']['prioridad'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                                        <option value="media" <?= ($configuraciones['temperatura']['prioridad'] ?? '') === 'media' ? 'selected' : '' ?>>Media</option>
                                        <option value="alta" <?= ($configuraciones['temperatura']['prioridad'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Ritmo Cardíaco -->
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">Ritmo Cardíaco</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Valor Mínimo (bpm)</label>
                                            <input type="number" class="form-control" name="ritmo_cardiaco[min]" 
                                                   value="<?= $configuraciones['ritmo_cardiaco']['min'] ?? 60 ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Valor Máximo (bpm)</label>
                                            <input type="number" class="form-control" name="ritmo_cardiaco[max]" 
                                                   value="<?= $configuraciones['ritmo_cardiaco']['max'] ?? 100 ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prioridad</label>
                                    <select class="form-select" name="ritmo_cardiaco[prioridad]" required>
                                        <option value="baja" <?= ($configuraciones['ritmo_cardiaco']['prioridad'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                                        <option value="media" <?= ($configuraciones['ritmo_cardiaco']['prioridad'] ?? '') === 'media' ? 'selected' : '' ?>>Media</option>
                                        <option value="alta" <?= ($configuraciones['ritmo_cardiaco']['prioridad'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Batería -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Batería</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Valor Mínimo (%)</label>
                                            <input type="number" class="form-control" name="bateria[min]" 
                                                   value="<?= $configuraciones['bateria']['min'] ?? 20 ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Valor Máximo (%)</label>
                                            <input type="number" class="form-control" name="bateria[max]" 
                                                   value="<?= $configuraciones['bateria']['max'] ?? 100 ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prioridad</label>
                                    <select class="form-select" name="bateria[prioridad]" required>
                                        <option value="baja" <?= ($configuraciones['bateria']['prioridad'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                                        <option value="media" <?= ($configuraciones['bateria']['prioridad'] ?? '') === 'media' ? 'selected' : '' ?>>Media</option>
                                        <option value="alta" <?= ($configuraciones['bateria']['prioridad'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Inactividad -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Inactividad</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tiempo Mínimo (minutos)</label>
                                            <input type="number" class="form-control" name="inactividad[min]" 
                                                   value="<?= $configuraciones['inactividad']['min'] ?? 30 ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tiempo Máximo (minutos)</label>
                                            <input type="number" class="form-control" name="inactividad[max]" 
                                                   value="<?= $configuraciones['inactividad']['max'] ?? 120 ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prioridad</label>
                                    <select class="form-select" name="inactividad[prioridad]" required>
                                        <option value="baja" <?= ($configuraciones['inactividad']['prioridad'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                                        <option value="media" <?= ($configuraciones['inactividad']['prioridad'] ?? '') === 'media' ? 'selected' : '' ?>>Media</option>
                                        <option value="alta" <?= ($configuraciones['inactividad']['prioridad'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resto del contenido de alertas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Filtros -->
            <form class="row g-2 mb-3">
                <div class="col-md-3">
                    <select class="form-select" name="tipo_alerta">
                        <option value="">Tipo de alerta</option>
                        <option value="temperatura">Temperatura</option>
                        <option value="ritmo_cardiaco">Ritmo cardíaco</option>
                        <option value="bateria">Batería baja</option>
                        <option value="inactividad">Inactividad</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="estado">
                        <option value="">Estado</option>
                        <option value="nueva">Nueva</option>
                        <option value="leida">Leída</option>
                        <option value="atendida">Atendida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="mascota">
                        <option value="">Mascota</option>
                        <?php foreach ($mascotas as $mascota): ?>
                            <option value="<?= $mascota['id'] ?>"><?= htmlspecialchars($mascota['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="fecha">
                </div>
            </form>

            <div class="table-responsive p-0" style="overflow-y:unset; max-height:none;">
                <table class="tabla-app table table-hover align-middle" id="tablaAlertas" style="min-width:1000px;">
                    <thead>
                        <tr>
                            <th style="width: 48px;">ID</th>
                            <th style="width: 180px;">Fecha de creación</th>
                            <th>Dispositivo</th>
                            <th>Mascota</th>
                            <th>Dueño</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Prioridad</th>
                            <th>Leída</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alertas)): ?>
                            <?php foreach ($alertas as $alerta): ?>
                            <tr>
                                <td><?= htmlspecialchars($alerta['id']) ?></td>
                                <td><?= htmlspecialchars($alerta['fecha_creacion']) ?></td>
                                <td><?= htmlspecialchars($alerta['dispositivo_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($alerta['mascota_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($alerta['propietario_nombre'] ?? '-') ?></td>
                                <td>
                                    <?php if ($alerta['tipo'] == 'temperatura'): ?>
                                        <span class="badge bg-danger"><i class="fas fa-thermometer-half"></i> Temperatura</span>
                                    <?php elseif ($alerta['tipo'] == 'ritmo_cardiaco'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-heartbeat"></i> Ritmo</span>
                                    <?php elseif ($alerta['tipo'] == 'bateria'): ?>
                                        <span class="badge bg-info text-dark"><i class="fas fa-battery-quarter"></i> Batería</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-unlink"></i> Inactividad</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($alerta['mensaje'] ?? '-') ?></td>
                                <td>
                                    <?php if ($alerta['prioridad'] == 'alta'): ?>
                                        <span class="badge bg-danger">Alta</span>
                                    <?php elseif ($alerta['prioridad'] == 'media'): ?>
                                        <span class="badge bg-warning text-dark">Media</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Baja</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($alerta['leida']) && $alerta['leida']): ?>
                                        <span class="badge bg-primary">Leída</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nueva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detalleAlerta<?= $alerta['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="marcarAtendida(<?= $alerta['id'] ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal de detalle de alerta -->
                            <div class="modal fade" id="detalleAlerta<?= $alerta['id'] ?>" tabindex="-1" aria-labelledby="detalleAlertaLabel<?= $alerta['id'] ?>" aria-hidden="true" data-bs-backdrop="false">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="detalleAlertaLabel<?= $alerta['id'] ?>">Detalle de la Alerta</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                  </div>
                                  <div class="modal-body">
                                    <p><strong>Tipo:</strong> <?= htmlspecialchars($alerta['tipo']) ?></p>
                                    <p><strong>Mensaje:</strong> <?= htmlspecialchars($alerta['mensaje']) ?></p>
                                    <p><strong>Fecha/Hora:</strong> <?= htmlspecialchars($alerta['fecha_creacion']) ?></p>
                                    <p><strong>Mascota:</strong> <?= htmlspecialchars($alerta['mascota_nombre'] ?? '-') ?></p>
                                    <p><strong>Dispositivo:</strong> <?= htmlspecialchars($alerta['dispositivo_nombre'] ?? '-') ?></p>
                                    <p><strong>Propietario:</strong> <?= htmlspecialchars($alerta['propietario_nombre'] ?? '-') ?></p>
                                    <p><strong>Prioridad:</strong> <?= htmlspecialchars($alerta['prioridad']) ?></p>
                                    <p><strong>Estado:</strong> <?= isset($alerta['leida']) && $alerta['leida'] ? 'Leída' : 'Nueva' ?></p>
                                    <!-- Aquí puedes agregar historial reciente si lo tienes disponible -->
                                  </div>
                                  <div class="modal-footer">
                                    <button class="btn btn-success" onclick="marcarAtendida(<?= $alerta['id'] ?>)">Marcar como atendida</button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay alertas registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formConfiguracionAlertas').addEventListener('submit', function(e) {
    e.preventDefault();
    // Validar que los valores mínimos sean menores que los máximos
    const tipos = ['temperatura', 'ritmo_cardiaco', 'bateria', 'inactividad'];
    let hayError = false;
    tipos.forEach(tipo => {
        const min = parseFloat(this[tipo + '[min]'].value);
        const max = parseFloat(this[tipo + '[max]'].value);
        if (min >= max) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `El valor mínimo de ${tipo} debe ser menor que el máximo`
            });
            hayError = true;
        }
    });
    if (hayError) return;
    // Enviar por AJAX
    const formData = new FormData(this);
    fetch('/proyecto-2/configuracion-alerta/actualizar-general', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message
            });
            // Cerrar el modal después de un breve tiempo
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfiguracionAlertas'));
                if (modal) modal.hide();
            }, 1200);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Error al guardar la configuración'
            });
        }
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar la configuración'
        });
    });
});

function mostrarNotificacion(mensaje, tipo) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${tipo} border-0 position-fixed bottom-0 end-0 m-3`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${mensaje}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function marcarAtendida(id) {
    // Aquí va la lógica AJAX para marcar la alerta como atendida
    alert('Marcar como atendida: ' + id);
}
</script>
