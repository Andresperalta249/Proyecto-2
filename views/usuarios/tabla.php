<table class="table" id="tablaUsuarios">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th>Último Acceso</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($usuarios)): ?>
            <tr>
                <td colspan="9">No hay usuarios registrados.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td data-label="ID"><?= $usuario['id_usuario'] ?></td>
                    <td data-label="Nombre"><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($usuario['email']) ?></td>
                    <td data-label="Rol"><?= htmlspecialchars($usuario['rol_nombre'] ?? '-') ?></td>
                    <td data-label="Teléfono"><?= htmlspecialchars($usuario['telefono'] ?? '-') ?></td>
                    <td data-label="Dirección"><?= htmlspecialchars($usuario['direccion'] ?? '-') ?></td>
                    <td data-label="Estado">
                        <label class="status-switch" data-tooltip="<?= $usuario['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>">
                            <input type="checkbox" class="cambiar-estado-usuario" 
                                data-id="<?= $usuario['id_usuario'] ?>"
                                <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                        </label>
                    </td>
                    <td data-label="Último Acceso">
                        <?php if ($usuario['ultimo_acceso']): ?>
                            <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) ?>
                        <?php else: ?>
                            <span class="status-badge badge-warning">Nunca</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acciones">
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" onclick="editarUsuario(<?= $usuario['id_usuario'] ?>)" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="eliminarUsuario(<?= $usuario['id_usuario'] ?>)" title="Eliminar usuario">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table> 