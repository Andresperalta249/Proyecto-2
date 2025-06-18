<div class="table-responsive-container">
    <table class="table" id="tablaUsuarios">
        <thead class="table__header">
            <tr class="table__row-header">
                <th class="table__cell table__cell--header">ID</th>
                <th class="table__cell table__cell--header">Nombre</th>
                <th class="table__cell table__cell--header">Email</th>
                <th class="table__cell table__cell--header">Rol</th>
                <th class="table__cell table__cell--header">Teléfono</th>
                <th class="table__cell table__cell--header">Dirección</th>
                <th class="table__cell table__cell--header">Estado</th>
                <th class="table__cell table__cell--header">Último Acceso</th>
                <th class="table__cell table__cell--header">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr class="table__row">
                    <td class="table__cell" colspan="9">No hay usuarios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr class="table__row">
                        <td class="table__cell" data-label="ID"><?= $usuario['id_usuario'] ?></td>
                        <td class="table__cell" data-label="Nombre"><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td class="table__cell" data-label="Email"><?= htmlspecialchars($usuario['email']) ?></td>
                        <td class="table__cell" data-label="Rol"><?= htmlspecialchars($usuario['rol_nombre'] ?? '-') ?></td>
                        <td class="table__cell" data-label="Teléfono"><?= htmlspecialchars($usuario['telefono'] ?? '-') ?></td>
                        <td class="table__cell" data-label="Dirección"><?= htmlspecialchars($usuario['direccion'] ?? '-') ?></td>
                        <td class="table__cell" data-label="Estado">
                            <label class="status-switch" data-tooltip="<?= $usuario['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>">
                                <input type="checkbox" class="cambiar-estado-usuario" 
                                    data-id="<?= $usuario['id_usuario'] ?>"
                                    <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>>
                                <span class="switch-slider"></span>
                            </label>
                        </td>
                        <td class="table__cell" data-label="Último Acceso">
                            <?php if ($usuario['ultimo_acceso']): ?>
                                <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) ?>
                            <?php else: ?>
                                <span class="status-badge badge-warning">Nunca</span>
                            <?php endif; ?>
                        </td>
                        <td class="table__cell" data-label="Acciones">
                            <div class="d-flex gap-2">
                                <button class="btn btn--info btn--sm" onclick="editarUsuario(<?= $usuario['id_usuario'] ?>)" title="Editar usuario">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn--danger btn--sm" onclick="eliminarUsuario(<?= $usuario['id_usuario'] ?>)" title="Eliminar usuario">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div> 