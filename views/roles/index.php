<?php
$titulo = "Gestión de Roles";
$subtitulo = "Administración de roles y permisos del sistema.";
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaRoles" class="table table-bordered table-striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Usuarios</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para la configuración que necesita el JS -->
<div id="roles-config" 
     data-app-url="<?= APP_URL ?>"
     data-permiso-editar="<?= verificarPermiso('editar_roles') ? 'true' : 'false' ?>"
     data-permiso-eliminar="<?= verificarPermiso('eliminar_roles') ? 'true' : 'false' ?>">
</div>

<!-- Botón flotante para añadir nuevo rol -->
<button class="fab-btn" id="btnNuevoRolFlotante" aria-label="Nuevo Rol" title="Nuevo rol">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Nuevo Rol</span>
</button>

<!-- Inclusión del script JS centralizado para la página de roles -->
<script src="<?= APP_URL ?>/assets/js/roles.js"></script> 