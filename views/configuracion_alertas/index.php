<?php
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Configuración de Alertas</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaConfiguracion">
            <i class="fas fa-plus"></i> Nueva Configuración
        </button>
    </div>

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

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="tabla-app" id="tablaConfiguraciones">
                    <thead>
                        <tr>
                            <th>Dispositivo</th>
                            <th>Tipo de Alerta</th>
                            <th>Valor Mínimo</th>
                            <th>Valor Máximo</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($configuraciones)): ?>
                            <?php foreach ($configuraciones as $config): ?>
                            <tr>
                                <td><?= htmlspecialchars($config['dispositivo_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($config['tipo_alerta']) ?></td>
                                <td><?= htmlspecialchars($config['valor_minimo']) ?></td>
                                <td><?= htmlspecialchars($config['valor_maximo']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $config['prioridad'] === 'alta' ? 'danger' : ($config['prioridad'] === 'media' ? 'warning' : 'success') ?>">
                                        <?= ucfirst($config['prioridad']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               <?= $config['estado'] ? 'checked' : '' ?>
                                               onchange="cambiarEstado(<?= $config['id'] ?>, this)">
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editarConfiguracion(<?= htmlspecialchars(json_encode($config)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarConfiguracion(<?= $config['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay configuraciones registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <nav aria-label="Navegación de páginas" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Nueva Configuración -->
<div class="modal fade" id="modalNuevaConfiguracion" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Configuración de Alerta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/proyecto-2/configuracion-alerta/crear" method="POST" id="formNuevaConfiguracion">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dispositivo</label>
                        <select class="form-select" name="dispositivo_id" required>
                            <option value="">Seleccione un dispositivo</option>
                            <?php foreach ($dispositivos as $dispositivo): ?>
                                <option value="<?= $dispositivo['id'] ?>">
                                    <?= htmlspecialchars($dispositivo['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Alerta</label>
                        <select class="form-select" name="tipo_alerta" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="temperatura">Temperatura</option>
                            <option value="ritmo_cardiaco">Ritmo Cardíaco</option>
                            <option value="bateria">Batería</option>
                            <option value="inactividad">Inactividad</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor Mínimo</label>
                        <input type="number" class="form-control" name="valor_minimo" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor Máximo</label>
                        <input type="number" class="form-control" name="valor_maximo" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioridad</label>
                        <select class="form-select" name="prioridad" required>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
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

<!-- Modal Editar Configuración -->
<div class="modal fade" id="modalEditarConfiguracion" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Configuración de Alerta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/proyecto-2/configuracion-alerta/actualizar" method="POST" id="formEditarConfiguracion">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Valor Mínimo</label>
                        <input type="number" class="form-control" name="valor_minimo" id="edit_valor_minimo" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor Máximo</label>
                        <input type="number" class="form-control" name="valor_maximo" id="edit_valor_maximo" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioridad</label>
                        <select class="form-select" name="prioridad" id="edit_prioridad" required>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="estado" id="edit_estado">
                            <label class="form-check-label">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

function cambiarEstado(id, checkbox) {
    const estado = checkbox.checked;
    fetch(`/proyecto-2/configuracion-alerta/actualizar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: id,
            estado: estado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Estado actualizado correctamente', 'success');
        } else {
            mostrarNotificacion('Error al actualizar el estado', 'danger');
            checkbox.checked = !estado;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al actualizar el estado', 'danger');
        checkbox.checked = !estado;
    });
}

function editarConfiguracion(config) {
    document.getElementById('edit_id').value = config.id;
    document.getElementById('edit_valor_minimo').value = config.valor_minimo;
    document.getElementById('edit_valor_maximo').value = config.valor_maximo;
    document.getElementById('edit_prioridad').value = config.prioridad;
    document.getElementById('edit_estado').checked = config.estado;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEditarConfiguracion'));
    modal.show();
}

function eliminarConfiguracion(id) {
    if (confirm('¿Está seguro de eliminar esta configuración?')) {
        fetch(`/proyecto-2/configuracion-alerta/eliminar/${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Configuración eliminada correctamente', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                mostrarNotificacion('Error al eliminar la configuración', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error al eliminar la configuración', 'danger');
        });
    }
}

// Validación de formularios
document.getElementById('formNuevaConfiguracion').addEventListener('submit', function(e) {
    const valorMinimo = parseFloat(this.valor_minimo.value);
    const valorMaximo = parseFloat(this.valor_maximo.value);
    
    if (valorMinimo >= valorMaximo) {
        e.preventDefault();
        mostrarNotificacion('El valor mínimo debe ser menor que el valor máximo', 'danger');
    }
});

document.getElementById('formEditarConfiguracion').addEventListener('submit', function(e) {
    const valorMinimo = parseFloat(this.valor_minimo.value);
    const valorMaximo = parseFloat(this.valor_maximo.value);
    
    if (valorMinimo >= valorMaximo) {
        e.preventDefault();
        mostrarNotificacion('El valor mínimo debe ser menor que el valor máximo', 'danger');
    }
});
</script> 