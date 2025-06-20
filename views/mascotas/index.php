<?php
// Test: Esto es un comentario de prueba
require_once 'views/layouts/header.php';

$titulo = "Gestión de Mascotas";
$subtitulo = "Administración de mascotas y sus dispositivos de monitoreo.";
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaMascotas" class="table table-bordered table-striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Tamaño</th>
                            <th>Género</th>
                            <th>Propietario</th>
                            <th>Estado</th>
                            <th>Batería</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán con DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para la configuración que necesita el JS -->
<div id="mascotas-config" 
     data-app-url="<?= APP_URL ?>"
     data-permiso-editar="<?= verificarPermiso('editar_mascotas') ? 'true' : 'false' ?>"
     data-permiso-eliminar="<?= verificarPermiso('eliminar_mascotas') ? 'true' : 'false' ?>">
</div>

<!-- Botón flotante para añadir nueva mascota -->
<button class="fab-btn" id="btnNuevaMascotaFlotante" aria-label="Nueva Mascota" title="Nueva mascota">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Nueva Mascota</span>
</button>

<!-- Modal Mascota -->
<div class="modal fade" id="modalMascota" tabindex="-1" role="dialog" aria-labelledby="modalMascotaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMascotaLabel">Nueva Mascota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formMascota">
                <div class="modal-body">
                    <input type="hidden" id="id_mascota" name="id_mascota">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="especie">Especie</label>
                        <input type="text" class="form-control" id="especie" name="especie" required>
                    </div>
                    <div class="form-group">
                        <label for="tamano">Tamaño</label>
                        <select class="form-control" id="tamano" name="tamano">
                            <option value="">Seleccione</option>
                            <option value="pequeno">Pequeño</option>
                            <option value="mediano">Mediano</option>
                            <option value="grande">Grande</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="genero">Género</label>
                        <select class="form-control" id="genero" name="genero">
                            <option value="">Seleccione</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_propietario">Propietario</label>
                        <select class="form-control select2" id="id_propietario" name="id_propietario" style="width: 100%;">
                            <option value="">Seleccione un propietario</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>

<script src="<?= APP_URL ?>/assets/js/mascotas.js"></script>