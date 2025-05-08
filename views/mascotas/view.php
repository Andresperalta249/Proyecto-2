<div class="container">
    <div class="row">
        <!-- Información de la Mascota -->
        <div class="col-lg-4">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Información de la Mascota</h3>
                </div>
                <div class="card-body text-center">
                    <?php if ($mascota['imagen']): ?>
                        <img src="<?= BASE_URL ?>uploads/mascotas/<?= $mascota['imagen'] ?>" 
                             alt="<?= $mascota['nombre'] ?>" 
                             class="img-fluid rounded-circle mb-3" 
                             style="max-height: 200px;">
                    <?php else: ?>
                        <i class="fas fa-paw fa-5x text-muted mb-3"></i>
                    <?php endif; ?>
                    
                    <h4><?= $mascota['nombre'] ?></h4>
                    <p class="text-muted"><?= $mascota['especie'] ?> - <?= $mascota['raza'] ?></p>
                    
                    <div class="mt-4">
                        <p><strong>Edad:</strong> <?= calcularEdad($mascota['fecha_nacimiento']) ?></p>
                        <p><strong>Sexo:</strong> <?= $mascota['sexo'] ?></p>
                        <?php if ($mascota['peso']): ?>
                            <p><strong>Peso:</strong> <?= $mascota['peso'] ?> kg</p>
                        <?php endif; ?>
                        <?php if ($mascota['descripcion']): ?>
                            <p><strong>Descripción:</strong><br><?= $mascota['descripcion'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>mascotas/edit/<?= $mascota['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button onclick="deleteMascota(<?= $mascota['id'] ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispositivos Asociados -->
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-4">Dispositivos Asociados</h3>
                    <a href="<?= BASE_URL ?>dispositivos/create/<?= $mascota['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Asociar Dispositivo
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($dispositivos)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-microchip fa-3x mb-3"></i>
                            <p>No hay dispositivos asociados a esta mascota.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Última Lectura</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dispositivos as $dispositivo): ?>
                                        <tr>
                                            <td><?= $dispositivo['nombre'] ?></td>
                                            <td><?= $dispositivo['tipo'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($dispositivo['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= $dispositivo['ultima_lectura'] ? date('d/m/Y H:i', strtotime($dispositivo['ultima_lectura'])) : 'N/A' ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>monitor/view/<?= $dispositivo['id'] ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>dispositivos/edit/<?= $dispositivo['id'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteDispositivo(<?= $dispositivo['id'] ?>)" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historial Médico -->
            <div class="card shadow-lg border-0 rounded-lg mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-4">Historial Médico</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHistorialModal">
                        <i class="fas fa-plus"></i> Agregar Registro
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($historial)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-file-medical fa-3x mb-3"></i>
                            <p>No hay registros médicos para esta mascota.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($historial as $registro): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?= date('d/m/Y', strtotime($registro['fecha'])) ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h5><?= $registro['tipo'] ?></h5>
                                        <p><?= $registro['descripcion'] ?></p>
                                        <?php if ($registro['documento']): ?>
                                            <a href="<?= BASE_URL ?>uploads/historial/<?= $registro['documento'] ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank">
                                                <i class="fas fa-file-download"></i> Ver Documento
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Historial -->
<div class="modal fade" id="addHistorialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Registro Médico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addHistorialForm" onsubmit="return handleFormSubmit(this, '<?= BASE_URL ?>mascotas/addHistorial/<?= $mascota['id'] ?>')" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Registro</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccione...</option>
                            <option value="Vacuna">Vacuna</option>
                            <option value="Consulta">Consulta</option>
                            <option value="Cirugía">Cirugía</option>
                            <option value="Medicamento">Medicamento</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="documento" class="form-label">Documento (opcional)</label>
                        <input type="file" class="form-control" id="documento" name="documento" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX, JPG, PNG. Tamaño máximo: 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: -30px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child:before {
    bottom: 0;
}

.timeline-date {
    position: absolute;
    left: 0;
    top: 0;
    width: 100px;
    text-align: right;
    color: #6c757d;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}
</style>

<script>
function deleteMascota(id) {
    if (confirm('¿Está seguro de eliminar esta mascota? Esta acción no se puede deshacer.')) {
        fetch(`<?= BASE_URL ?>mascotas/delete/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Mascota eliminada correctamente', 'success');
                setTimeout(() => window.location.href = '<?= BASE_URL ?>mascotas', 1500);
            } else {
                showToast(data.message || 'Error al eliminar la mascota', 'danger');
            }
        })
        .catch(error => {
            showToast('Error al eliminar la mascota', 'danger');
        });
    }
}

function deleteDispositivo(id) {
    if (confirm('¿Está seguro de eliminar este dispositivo? Esta acción no se puede deshacer.')) {
        fetch(`<?= BASE_URL ?>dispositivos/delete/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Dispositivo eliminado correctamente', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast(data.message || 'Error al eliminar el dispositivo', 'danger');
            }
        })
        .catch(error => {
            showToast('Error al eliminar el dispositivo', 'danger');
        });
    }
}

// Validar documento al seleccionar
document.getElementById('documento').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) { // 10MB
            showToast('El documento no debe superar los 10MB', 'danger');
            this.value = '';
            return;
        }
        const allowedTypes = ['.pdf', '.doc', '.docx', '.jpg', '.jpeg', '.png'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExtension)) {
            showToast('Formato de archivo no permitido', 'danger');
            this.value = '';
            return;
        }
    }
});
</script> 