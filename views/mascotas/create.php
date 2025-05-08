<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Nueva Mascota</h3>
                </div>
                <div class="card-body">
                    <form id="createMascotaForm" onsubmit="return handleFormSubmit(this, '<?= BASE_URL ?>mascotas/create')" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="nombre" name="nombre" type="text" required />
                                    <label for="nombre">Nombre de la Mascota</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="especie" name="especie" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Perro">Perro</option>
                                        <option value="Gato">Gato</option>
                                        <option value="Ave">Ave</option>
                                        <option value="Roedor">Roedor</option>
                                        <option value="Reptil">Reptil</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <label for="especie">Especie</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="raza" name="raza" type="text" required />
                                    <label for="raza">Raza</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" type="date" required />
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="peso" name="peso" type="number" step="0.1" />
                                    <label for="peso">Peso (kg)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="sexo" name="sexo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Macho">Macho</option>
                                        <option value="Hembra">Hembra</option>
                                    </select>
                                    <label for="sexo">Sexo</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Foto de la Mascota</label>
                            <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="descripcion" name="descripcion" style="height: 100px"></textarea>
                            <label for="descripcion">Descripción</label>
                        </div>
                        <div class="mt-4 mb-0">
                            <div class="d-grid">
                                <button class="btn btn-primary btn-block" type="submit">Registrar Mascota</button>
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