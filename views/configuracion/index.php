<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Configuración del Sistema</h1>
        </div>
    </div>

    <!-- Configuración General -->
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-header">
            <h3 class="font-weight-light my-4">Configuración General</h3>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>configuracion/update" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_sistema">Nombre del Sistema</label>
                            <input type="text" class="form-control" id="nombre_sistema" name="nombre_sistema" 
                                   value="<?= $configuracion['nombre_sistema'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email_contacto">Email de Contacto</label>
                            <input type="email" class="form-control" id="email_contacto" name="email_contacto" 
                                   value="<?= $configuracion['email_contacto'] ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono_contacto">Teléfono de Contacto</label>
                            <input type="tel" class="form-control" id="telefono_contacto" name="telefono_contacto" 
                                   value="<?= $configuracion['telefono_contacto'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= $configuracion['direccion'] ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tiempo_actualizacion">Tiempo de Actualización (segundos)</label>
                            <input type="number" class="form-control" id="tiempo_actualizacion" name="tiempo_actualizacion" 
                                   value="<?= $configuracion['tiempo_actualizacion'] ?>" min="5" max="300" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dias_retener_logs">Días para Retener Logs</label>
                            <input type="number" class="form-control" id="dias_retener_logs" name="dias_retener_logs" 
                                   value="<?= $configuracion['dias_retener_logs'] ?>" min="1" max="365" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="notificaciones_email" 
                                       name="notificaciones_email" <?= $configuracion['notificaciones_email'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="notificaciones_email">Notificaciones por Email</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="notificaciones_push" 
                                       name="notificaciones_push" <?= $configuracion['notificaciones_push'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="notificaciones_push">Notificaciones Push</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="tema_oscuro" 
                                       name="tema_oscuro" <?= $configuracion['tema_oscuro'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="tema_oscuro">Tema Oscuro</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Gestión de Logs -->
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-header">
            <h3 class="font-weight-light my-4">Gestión de Logs</h3>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>configuracion/limpiarLogs" method="POST" class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dias">Limpiar logs más antiguos que (días)</label>
                            <input type="number" class="form-control" id="dias" name="dias" 
                                   value="30" min="1" max="365" required>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-broom"></i> Limpiar Logs
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Backup y Restauración -->
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-header">
            <h3 class="font-weight-light my-4">Backup y Restauración</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form action="<?= BASE_URL ?>configuracion/backup" method="POST">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Generar Backup
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="<?= BASE_URL ?>configuracion/restore" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="backup_file">Restaurar desde Backup</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="backup_file" name="backup_file" accept=".sql">
                                <label class="custom-file-label" for="backup_file">Seleccionar archivo</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-upload"></i> Restaurar Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar nombre del archivo seleccionado
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });

    // Validar formulario de configuración
    document.querySelector('form[action*="update"]').addEventListener('submit', function(e) {
        const tiempoActualizacion = document.getElementById('tiempo_actualizacion').value;
        const diasRetenerLogs = document.getElementById('dias_retener_logs').value;

        if (tiempoActualizacion < 5 || tiempoActualizacion > 300) {
            e.preventDefault();
            showToast('El tiempo de actualización debe estar entre 5 y 300 segundos', 'danger');
        }

        if (diasRetenerLogs < 1 || diasRetenerLogs > 365) {
            e.preventDefault();
            showToast('Los días para retener logs deben estar entre 1 y 365', 'danger');
        }
    });

    // Confirmar limpieza de logs
    document.querySelector('form[action*="limpiarLogs"]').addEventListener('submit', function(e) {
        if (!confirm('¿Está seguro de que desea limpiar los logs antiguos? Esta acción no se puede deshacer.')) {
            e.preventDefault();
        }
    });

    // Confirmar restauración de backup
    document.querySelector('form[action*="restore"]').addEventListener('submit', function(e) {
        if (!confirm('¿Está seguro de que desea restaurar el backup? Esta acción sobrescribirá los datos actuales.')) {
            e.preventDefault();
        }
    });
});
</script> 