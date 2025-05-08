<?php require_once 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Configuración de Notificaciones</h5>
                </div>
                <div class="card-body">
                    <form id="configuracionForm">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Tipos de Notificaciones</h6>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifAlertas" 
                                           name="tipos[alertas]" <?= $config['tipos']['alertas'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifAlertas">
                                        <i class="fas fa-exclamation-triangle text-danger"></i> Alertas
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Recibir notificaciones cuando se detecten alertas en tus dispositivos
                                    </small>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifDispositivos" 
                                           name="tipos[dispositivos]" <?= $config['tipos']['dispositivos'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifDispositivos">
                                        <i class="fas fa-microchip text-info"></i> Dispositivos
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Notificaciones sobre el estado y actualizaciones de tus dispositivos
                                    </small>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifMascotas" 
                                           name="tipos[mascotas]" <?= $config['tipos']['mascotas'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifMascotas">
                                        <i class="fas fa-paw text-success"></i> Mascotas
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Actualizaciones sobre el estado y actividades de tus mascotas
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="mb-3">Métodos de Notificación</h6>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifPush" 
                                           name="metodos[push]" <?= $config['metodos']['push'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifPush">
                                        <i class="fas fa-bell text-primary"></i> Notificaciones Push
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Recibir notificaciones en tiempo real en tu navegador
                                    </small>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifEmail" 
                                           name="metodos[email]" <?= $config['metodos']['email'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifEmail">
                                        <i class="fas fa-envelope text-secondary"></i> Correo Electrónico
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Recibir notificaciones por correo electrónico
                                    </small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="frecuenciaEmail">Frecuencia de Correos</label>
                                    <select class="form-select" id="frecuenciaEmail" name="frecuencia_email">
                                        <option value="inmediato" <?= $config['frecuencia_email'] === 'inmediato' ? 'selected' : '' ?>>
                                            Inmediato
                                        </option>
                                        <option value="diario" <?= $config['frecuencia_email'] === 'diario' ? 'selected' : '' ?>>
                                            Resumen Diario
                                        </option>
                                        <option value="semanal" <?= $config['frecuencia_email'] === 'semanal' ? 'selected' : '' ?>>
                                            Resumen Semanal
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Horario de Notificaciones</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="horaInicio">Hora de Inicio</label>
                                            <input type="time" class="form-control" id="horaInicio" 
                                                   name="hora_inicio" value="<?= $config['hora_inicio'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="horaFin">Hora de Fin</label>
                                            <input type="time" class="form-control" id="horaFin" 
                                                   name="hora_fin" value="<?= $config['hora_fin'] ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifUrgentes" 
                                           name="notif_urgentes" <?= $config['notif_urgentes'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifUrgentes">
                                        Recibir notificaciones urgentes en cualquier momento
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('configuracionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const config = {
        tipos: {
            alertas: formData.get('tipos[alertas]') === 'on',
            dispositivos: formData.get('tipos[dispositivos]') === 'on',
            mascotas: formData.get('tipos[mascotas]') === 'on'
        },
        metodos: {
            push: formData.get('metodos[push]') === 'on',
            email: formData.get('metodos[email]') === 'on'
        },
        frecuencia_email: formData.get('frecuencia_email'),
        hora_inicio: formData.get('hora_inicio'),
        hora_fin: formData.get('hora_fin'),
        notif_urgentes: formData.get('notif_urgentes') === 'on'
    };

    fetch('<?= BASE_URL ?>notificacion/guardarConfiguracion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast('success', 'Configuración guardada correctamente');
        } else {
            mostrarToast('error', 'Error al guardar la configuración');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al guardar la configuración');
    });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?> 