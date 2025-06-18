<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Editar Mascota</h3>
                </div>
                <div class="card-body">
                    <form id="editMascotaForm" onsubmit="return handleFormSubmit(this, '<?= BASE_URL ?>mascotas/edit/<?= $mascota['id_mascota'] ?>')" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="nombre" name="nombre" type="text" value="<?= $mascota['nombre'] ?>" required />
                                    <label for="nombre">Nombre de la Mascota</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="especie" name="especie" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Perro" <?= $mascota['especie'] === 'Perro' ? 'selected' : '' ?>>Perro</option>
                                        <option value="Gato" <?= $mascota['especie'] === 'Gato' ? 'selected' : '' ?>>Gato</option>
                                        <option value="Ave" <?= $mascota['especie'] === 'Ave' ? 'selected' : '' ?>>Ave</option>
                                        <option value="Roedor" <?= $mascota['especie'] === 'Roedor' ? 'selected' : '' ?>>Roedor</option>
                                        <option value="Reptil" <?= $mascota['especie'] === 'Reptil' ? 'selected' : '' ?>>Reptil</option>
                                        <option value="Otro" <?= $mascota['especie'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                    </select>
                                    <label for="especie">Especie</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="tamano" name="tamano" required>
                                        <option value="">Seleccione un tamaño</option>
                                        <option value="Pequeño" <?= $mascota['tamano'] === 'Pequeño' ? 'selected' : '' ?>>Pequeño</option>
                                        <option value="Mediano" <?= $mascota['tamano'] === 'Mediano' ? 'selected' : '' ?>>Mediano</option>
                                        <option value="Grande" <?= $mascota['tamano'] === 'Grande' ? 'selected' : '' ?>>Grande</option>
                                    </select>
                                    <label for="tamano">Tamaño</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="<?= $mascota['fecha_nacimiento'] ?>" required />
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Foto de la Mascota</label>
                            <?php if ($mascota['imagen']): ?>
                            <div class="mb-2 text-center">
                                <img src="<?= BASE_URL ?>uploads/mascotas/<?= $mascota['imagen'] ?>" 
                                     alt="<?= $mascota['nombre'] ?>" 
                                     class="img-thumbnail profile-image-thumbnail">
                            </div>
                            <?php endif; ?>
                            <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control form-control--textarea-lg" id="descripcion" name="descripcion"><?= $mascota['descripcion'] ?></textarea>
                            <label for="descripcion">Descripción</label>
                        </div>
                        <?php if (in_array('gestionar_mascotas', $_SESSION['permisos'] ?? [])): ?>
                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Propietario</label>
                            <select class="form-select select2" id="usuario_id" name="usuario_id" required>
                                <option value="">Seleccione un propietario</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id'] ?>" <?= $mascota['usuario_id'] == $usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo" <?= $mascota['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $mascota['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="mt-4 mb-0">
                            <div class="d-grid">
                                <button class="btn btn--primary w-100" type="submit">Actualizar Mascota</button>
                            </div>
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