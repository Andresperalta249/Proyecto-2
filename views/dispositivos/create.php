<?php
$puedeAsignarUsuario = verificarPermiso('ver_todos_dispositivo');
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-4">Nuevo Dispositivo</h3>
                </div>
                <div class="card-body">
                    <form id="createDispositivoForm" onsubmit="return handleFormSubmit(this, '<?= BASE_URL ?>dispositivos/create')">
                        <div class="row mb-3">
                            <?php if ($puedeAsignarUsuario): ?>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="usuario_id" name="usuario_id" required>
                                        <option value="">Seleccione un usuario...</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="usuario_id">Usuario Asignado</label>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="nombre" name="nombre" type="text" required />
                                    <label for="nombre">Nombre del Dispositivo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="tipo" name="tipo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="GPS">GPS</option>
                                        <option value="Sensores">Sensores</option>
                                        <option value="Cámara">Cámara</option>
                                        <option value="Comedor">Comedor Automático</option>
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
                                            <option value="<?= $mascota['id'] ?>" data-usuario="<?= $mascota['usuario_id'] ?? $mascota['propietario_id'] ?>">
                                                <?= htmlspecialchars($mascota['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="mascota_id">Mascota Asociada</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="descripcion" name="descripcion" style="height: 100px"></textarea>
                            <label for="descripcion">Descripción</label>
                        </div>
                        <div class="mt-4 mb-0">
                            <div class="d-grid">
                                <button class="btn btn-primary btn-block" type="submit">Registrar Dispositivo</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    <?php if ($puedeAsignarUsuario): ?>
    // Filtrar mascotas según usuario seleccionado
    $('#usuario_id').on('change', function() {
        var usuarioId = $(this).val();
        $('#mascota_id option').each(function() {
            var mascotaUsuario = $(this).data('usuario');
            if (!usuarioId || !mascotaUsuario || mascotaUsuario == usuarioId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        $('#mascota_id').val('');
    });
    <?php else: ?>
    // Si no puede asignar usuario, filtrar solo las mascotas propias
    var usuarioId = <?= json_encode($_SESSION['user_id']) ?>;
    $('#mascota_id option').each(function() {
        var mascotaUsuario = $(this).data('usuario');
        if (!mascotaUsuario || mascotaUsuario == usuarioId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    <?php endif; ?>
});
</script> 