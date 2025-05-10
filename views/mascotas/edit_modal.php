<form id="formEditarMascota" autocomplete="off">
    <input type="hidden" name="id" value="<?= htmlspecialchars($mascota['id'] ?? '') ?>">
    <div class="mb-3">
        <label for="editar_nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="editar_nombre" name="nombre" value="<?= htmlspecialchars($mascota['nombre'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
        <label for="editar_especie" class="form-label">Especie</label>
        <select class="form-select" id="editar_especie" name="especie" required>
            <option value="">Seleccione una especie</option>
            <option value="Perro" <?= ($mascota['especie'] ?? '') === 'Perro' ? 'selected' : '' ?>>Perro</option>
            <option value="Gato" <?= ($mascota['especie'] ?? '') === 'Gato' ? 'selected' : '' ?>>Gato</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="editar_tamano" class="form-label">Tamaño</label>
        <select class="form-select" id="editar_tamano" name="tamano" required>
            <option value="">Seleccione un tamaño</option>
            <option value="Pequeño" <?= ($mascota['tamano'] ?? '') === 'Pequeño' ? 'selected' : '' ?>>Pequeño</option>
            <option value="Mediano" <?= ($mascota['tamano'] ?? '') === 'Mediano' ? 'selected' : '' ?>>Mediano</option>
            <option value="Grande" <?= ($mascota['tamano'] ?? '') === 'Grande' ? 'selected' : '' ?>>Grande</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="editar_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
        <input type="date" class="form-control" id="editar_fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($mascota['fecha_nacimiento'] ?? '') ?>" required>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
    <div id="alertaEditarMascota" class="mt-2"></div>
</form>
<script>
$(function() {
    $('#formEditarMascota').off('submit').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '/proyecto-2/mascotas/edit/' + $('input[name=id]').val(),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#alertaEditarMascota').html('<div class="alert alert-success">' + (response.message || 'Mascota actualizada correctamente') + '</div>');
                    setTimeout(function() {
                        $('#modalEditarMascota').modal('hide');
                        recargarTablaMascotas();
                    }, 1200);
                } else {
                    $('#alertaEditarMascota').html('<div class="alert alert-danger">' + (response.error || 'Error al actualizar la mascota') + '</div>');
                }
            },
            error: function() {
                $('#alertaEditarMascota').html('<div class="alert alert-danger">No se pudo actualizar la mascota. Intenta de nuevo.</div>');
            }
        });
    });
});
</script> 