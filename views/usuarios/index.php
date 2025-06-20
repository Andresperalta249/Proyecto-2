<?php
$titulo = "Gestión de Usuarios";
$subtitulo = "Administración de usuarios y sus roles en el sistema.";
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaUsuarios" class="table table-bordered table-striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Dirección</th>
                            <th>Estado</th>
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
<div id="usuarios-config" 
     data-app-url="<?= APP_URL ?>"
     data-permiso-editar="<?= verificarPermiso('editar_usuarios') ? 'true' : 'false' ?>"
     data-permiso-eliminar="<?= verificarPermiso('eliminar_usuarios') ? 'true' : 'false' ?>">
</div>

<!-- Botón flotante para añadir nuevo usuario -->
<button class="fab-btn" id="btnNuevoUsuarioFlotante" aria-label="Nuevo Usuario" title="Nuevo usuario">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Nuevo Usuario</span>
</button>

<!-- Inclusión del script JS centralizado para la página de usuarios -->
<script src="<?= APP_URL ?>/assets/js/usuarios.js"></script> 