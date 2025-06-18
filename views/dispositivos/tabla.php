<?php
// Permisos del usuario
$puedeEditar = in_array('editar_dispositivos', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_dispositivos', $_SESSION['permissions'] ?? []);
?>

<div class="table-responsive">
    <table class="tabla-app" id="tablaDispositivos">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>MAC</th>
                <th>Dueño</th>
                <th>Disponible</th>
                <th>Estado</th>
                <th>Batería</th>
                <th>Mascota</th>
                <th>Última Lectura</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($dispositivos)): ?>
                <?php foreach ($dispositivos as $dispositivo): ?>
                    <tr class="fila-dispositivo" data-id="<?= $dispositivo['id'] ?>">
                        <td class="text-primary text-center"><?= $dispositivo['id'] ?></td>
                        <td class="nombre-dispositivo" title="<?= htmlspecialchars($dispositivo['nombre']) ?>">
                            <?= htmlspecialchars($dispositivo['nombre']) ?>
                        </td>
                        <td><?= htmlspecialchars($dispositivo['mac']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['usuario_nombre'] ?? $dispositivo['propietario_nombre'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php if (empty($dispositivo['mascota_nombre'])): ?>
                                <span class="status-badge badge-success">Disponible</span>
                            <?php else: ?>
                                <span class="status-badge badge-warning">Asignado</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= htmlspecialchars($dispositivo['estado'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php
                            $bateria = isset($dispositivo['bateria']) ? (int)$dispositivo['bateria'] : null;
                            if ($bateria === null || $bateria === '') {
                                echo '-';
                            } else {
                                echo '<span class="fw-medium">' . $bateria . '%</span>';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($dispositivo['mascota_nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($dispositivo['ultima_lectura'] ?? '-') ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($puedeEditar): ?>
                                <button class="btn-accion btn-info me-1 btnEditarDispositivo" data-id="<?= $dispositivo['id'] ?>" title="Editar" data-bs-toggle="tooltip">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($puedeEliminar): ?>
                                <button class="btn-accion btn-danger me-1 btnEliminarDispositivo" data-id="<?= $dispositivo['id'] ?>" title="Eliminar" data-bs-toggle="tooltip">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($dispositivo['estado'] === 'activo' && !empty($dispositivo['mascota_nombre'])): ?>
                                    <button class="btn-accion btn-success btnMonitorDispositivo" data-id="<?= $dispositivo['id'] ?>" title="Monitor" data-bs-toggle="tooltip">
                                        <i class="fas fa-desktop"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-accion btn-secondary" disabled title="Sin dispositivo asociado o inactivo" data-bs-toggle="tooltip">
                                        <i class="fas fa-desktop"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        No hay dispositivos registrados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div> 