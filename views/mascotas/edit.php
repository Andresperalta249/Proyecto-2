<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Editar Mascota</h3>
                </div>
                <div class="card-body">
                    <form id="editMascotaForm" onsubmit="return handleFormSubmit(this, '<?= BASE_URL ?>mascotas/edit/<?= $mascota['id'] ?>')" enctype="multipart/form-data">
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
                                    <input class="form-control" id="raza" name="raza" type="text" value="<?= $mascota['raza'] ?>" required />
                                    <label for="raza">Raza</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="<?= $mascota['fecha_nacimiento'] ?>" required />
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="peso" name="peso" type="number" step="0.1" value="<?= $mascota['peso'] ?>" />
                                    <label for="peso">Peso (kg)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="sexo" name="sexo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Macho" <?= $mascota['sexo'] === 'Macho' ? 'selected' : '' ?>>Macho</option>
                                        <option value="Hembra" <?= $mascota['sexo'] === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                                    </select>
                                    <label for="sexo">Sexo</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Foto de la Mascota</label>
                            <?php if ($mascota['imagen']): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>uploads/mascotas/<?= $mascota['imagen'] ?>" 
                                     alt="<?= $mascota['nombre'] ?>" 
                                     class="img-thumbnail" 
                                     style="max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="descripcion" name="descripcion" style="height: 100px"><?= $mascota['descripcion'] ?></textarea>
                            <label for="descripcion">Descripción</label>
                        </div>
                        <div class="mt-4 mb-0">
                            <div class="d-grid">
                                <button class="btn btn-primary btn-block" type="submit">Actualizar Mascota</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
});
</script> 