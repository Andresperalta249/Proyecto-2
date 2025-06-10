<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Editar Dispositivo</h3>
                </div>
                <div class="card-body">
                    <form id="editDispositivoForm" onsubmit="return handleFormSubmit(this, '<?= BASE_URL ?>dispositivos/edit/<?= $dispositivo['id_dispositivo'] ?>')">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="nombre" name="nombre" type="text" value="<?= $dispositivo['nombre'] ?>" required />
                                    <label for="nombre">Nombre del Dispositivo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="tipo" name="tipo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="GPS" <?= $dispositivo['tipo'] === 'GPS' ? 'selected' : '' ?>>GPS</option>
                                        <option value="Sensores" <?= $dispositivo['tipo'] === 'Sensores' ? 'selected' : '' ?>>Sensores</option>
                                        <option value="Cámara" <?= $dispositivo['tipo'] === 'Cámara' ? 'selected' : '' ?>>Cámara</option>
                                        <option value="Comedor" <?= $dispositivo['tipo'] === 'Comedor' ? 'selected' : '' ?>>Comedor Automático</option>
                                    </select>
                                    <label for="tipo">Tipo de Dispositivo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="mascota_id" name="mascota_id" required>
                                        <option value="">Seleccione una mascota...</option>
                                        <?php foreach ($mascotas as $mascota): ?>
                                            <option value="<?= $mascota['id_mascota'] ?>" <?= $dispositivo['mascota_id'] == $mascota['id_mascota'] ? 'selected' : '' ?> >
                                                <?= $mascota['nombre'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="mascota_id">Mascota Asociada</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="activo" <?= $dispositivo['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= $dispositivo['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                    <label for="estado">Estado</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="descripcion" name="descripcion" style="height: 100px"><?= $dispositivo['descripcion'] ?></textarea>
                            <label for="descripcion">Descripción</label>
                        </div>
                        <div class="mt-4 mb-0">
                            <div class="d-grid">
                                <button class="btn btn-primary btn-block" type="submit">Actualizar Dispositivo</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 