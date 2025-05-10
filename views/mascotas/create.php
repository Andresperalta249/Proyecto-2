<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Nueva Mascota</h3>
                </div>
                <div class="card-body">
                    <form id="formMascota">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="especie" class="form-label">Especie</label>
                            <select class="form-select" id="especie" name="especie" required>
                                <option value="">Seleccione una especie</option>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tamano" class="form-label">Tamaño</label>
                            <select class="form-select" id="tamano" name="tamano" required>
                                <option value="">Seleccione un tamaño</option>
                                <option value="Pequeño">Pequeño</option>
                                <option value="Mediano">Mediano</option>
                                <option value="Grande">Grande</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                        </div>
                        <?php if (in_array('gestionar_mascotas', $_SESSION['permisos'] ?? [])): ?>
                        <div class="mb-3">
                            <label for="propietario_id" class="form-label">Propietario</label>
                            <select class="form-select select2" id="propietario_id" name="propietario_id" required>
                                <option value="">Seleccione un propietario</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validar tamaño y tipo de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) { // 5MB
                showToast('La imagen no debe superar los 5MB', 'danger');
                this.value = '';
                return;
            }
            if (!file.type.match('image.*')) {
                showToast('Solo se permiten archivos de imagen', 'danger');
                this.value = '';
                return;
            }
        }
    });

    // Inicializar select2 para propietario
    if (window.jQuery && $('.select2').length) {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Buscar propietario...',
            allowClear: true
        });
    }
});
</script> 