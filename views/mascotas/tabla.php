<?php
// Permisos del usuario
$puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
$puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
?>

<div class="table-responsive-container">
    <table class="table" id="tablaMascotas">
        <thead class="table__header">
            <tr class="table__row-header">
                <th class="table__cell table__cell--header">ID</th>
                <th class="table__cell table__cell--header">Nombre</th>
                <th class="table__cell table__cell--header">Especie</th>
                <th class="table__cell table__cell--header">Tamaño</th>
                <th class="table__cell table__cell--header">Género</th>
                <th class="table__cell table__cell--header">Propietario</th>
                <th class="table__cell table__cell--header">Edad</th>
                <th class="table__cell table__cell--header">Batería</th>
                <th class="table__cell table__cell--header">Estado</th>
                <th class="table__cell table__cell--header">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($mascotas)): ?>
                <?php foreach ($mascotas as $mascota): ?>
                    <tr class="table__row">
                        <td class="table__cell table__cell--w-48" data-label="ID"><?= $mascota['id_mascota'] ?></td>
                        <td class="table__cell table__cell--ellipsis" data-label="Nombre" title="<?= htmlspecialchars($mascota['nombre']) ?>"><?= htmlspecialchars($mascota['nombre']) ?></td>
                        <td class="table__cell table__cell--w-80" data-label="Especie"><?= htmlspecialchars($mascota['especie']) ?></td>
                        <td class="table__cell table__cell--w-80" data-label="Tamaño"><?= htmlspecialchars($mascota['tamano']) ?></td>
                        <td class="table__cell table__cell--w-60" data-label="Género"><?= htmlspecialchars($mascota['genero'] ?? '-') ?></td>
                        <td class="table__cell table__cell--ellipsis" data-label="Propietario" title="<?= $propietario ?>"><?= $propietario ?: '-' ?></td>
                        <td class="table__cell table__cell--w-60 text-center" data-label="Edad">
                            <?php
                            if (!empty($mascota['fecha_nacimiento'])) {
                                $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                                $hoy = new DateTime();
                                $edad = $hoy->diff($nacimiento)->y;
                                echo $edad . ' año' . ($edad != 1 ? 's' : '');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="table__cell table__cell--w-80 text-center" data-label="Batería">
                            <?php
                            $bateria = isset($mascota['bateria']) ? (int)$mascota['bateria'] : null;
                            if ($bateria === null || $bateria === '') {
                                echo '-';
                            } else {
                                echo '<span class="text-dark text-semibold">' . $bateria . '%</span>';
                            }
                            ?>
                        </td>
                        <td class="table__cell table__cell--w-110" data-label="Estado">
                            <div class="form-check form-switch d-flex align-items-center mb-0">
                                <input class="form-check-input cambiar-estado-mascota <?= $mascota['estado'] === 'inactivo' ? 'switch-inactivo' : '' ?>"
                                    type="checkbox"
                                    data-id="<?= $mascota['id_mascota'] ?>"
                                    <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?> >
                                <label class="form-check-label ms-2">
                                    <?= ucfirst($mascota['estado']) ?>
                                </label>
                            </div>
                        </td>
                        <td class="table__cell table__cell--w-90" data-label="Acciones">
                            <div class="d-flex gap-2">
                                <?php if (
                                    $puedeEditarCualquiera ||
                                    ($puedeEditarPropias && $mascota['usuario_id'] == $_SESSION['propietario_id'])
                                ): ?>
                                <button class="btn btn--info btn--sm btnEditarMascota" data-id="<?= $mascota['id_mascota'] ?>" title="Editar" data-bs-toggle="tooltip">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($puedeEliminar): ?>
                                <button class="btn btn--danger btn--sm btnEliminarMascota" data-id="<?= $mascota['id_mascota'] ?>" title="Eliminar" data-bs-toggle="tooltip">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="table__row">
                    <td class="table__cell text-center text-muted py-4" colspan="10">
                        No hay mascotas registradas.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div> 