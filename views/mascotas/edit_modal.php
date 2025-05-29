<form id="formMascota" autocomplete="off">
    <input type="hidden" name="id" value="<?= htmlspecialchars($mascota['id'] ?? '') ?>">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($mascota['nombre'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label for="especie" class="form-label">Especie</label>
        <select class="form-select" id="especie" name="especie" required>
            <option value="">Seleccione especie</option>
            <option value="perro" <?= ($mascota['especie'] ?? '') === 'perro' ? 'selected' : '' ?>>Perro</option>
            <option value="gato" <?= ($mascota['especie'] ?? '') === 'gato' ? 'selected' : '' ?>>Gato</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="tamano" class="form-label">Tamaño</label>
        <select class="form-select" id="tamano" name="tamano" required>
            <option value="">Seleccione un tamaño</option>
            <option value="Pequeño" <?= ($mascota['tamano'] ?? '') === 'Pequeño' ? 'selected' : '' ?>>Pequeño</option>
            <option value="Mediano" <?= ($mascota['tamano'] ?? '') === 'Mediano' ? 'selected' : '' ?>>Mediano</option>
            <option value="Grande" <?= ($mascota['tamano'] ?? '') === 'Grande' ? 'selected' : '' ?>>Grande</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($mascota['fecha_nacimiento'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label for="genero" class="form-label">Género</label>
        <select class="form-select" id="genero" name="genero" required>
            <option value="">Seleccione género</option>
            <option value="macho" <?= ($mascota['genero'] ?? '') === 'macho' ? 'selected' : '' ?>>Macho</option>
            <option value="hembra" <?= ($mascota['genero'] ?? '') === 'hembra' ? 'selected' : '' ?>>Hembra</option>
        </select>
    </div>
    <?php 
    $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
    $puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
    if ($esAdmin || $puedeEditarCualquiera): 
    ?>
    <div class="mb-3">
        <label for="propietario_id" class="form-label">Propietario</label>
        <select class="form-select select2" id="propietario_id" name="propietario_id" required>
            <option value="">Seleccione un propietario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['id'] ?>" <?= ($mascota['propietario_id'] ?? '') == $usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="estado" class="form-label">Estado</label>
        <select class="form-select" id="estado" name="estado" required>
            <option value="activo" <?= ($mascota['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= ($mascota['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>
    <?php endif; ?>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
    <div id="alertaMascota" class="mt-2"></div>
</form>

<script>
// Asegurarse de que jQuery y select2 estén disponibles
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        setTimeout(function() {
            if ($('.select2').length && $.fn.select2) {
                $('.select2').select2({
                    dropdownParent: $('#modalMascota')
                });
            }
        }, 100); // Pequeño retraso para asegurar que el DOM y select2 estén listos
    });
} else {
    console.error('jQuery no está disponible');
}
</script> 