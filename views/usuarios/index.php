<?php
// DEPURACIÓN: Esto es una prueba de contenido visible desde la vista de usuarios
?>
<?php $subtitulo = isset($subtitulo) ? $subtitulo : 'Gestiona, busca y administra los usuarios del sistema.'; ?>
<p class="subtitle text-md" style="margin-top: 0; margin-bottom: 0;">
  <?= htmlspecialchars($subtitulo) ?>
</p>

<div class="table-container">
    <div class="search-filters">
        <input type="text" id="buscar" class="search-input" placeholder="Buscar por nombre o email...">
        <select id="rol" class="filter-select">
            <option value="">Todos los roles</option>
            <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="estado" class="filter-select">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
    </div>
    <?php 
    // Debug: Verificar si hay usuarios
    if (isset($usuarios)) {
        echo "<!-- Debug: Número de usuarios: " . count($usuarios) . " -->";
    } else {
        echo "<!-- Debug: Variable usuarios no está definida -->";
    }
    require __DIR__ . '/tabla.php'; 
    ?>
</div>

<div class="pagination-container">
    <div class="pagination-info">
        Mostrando <?= count($usuarios) ?> de <?= $totalUsuarios ?> registros
    </div>
    <div class="pagination-buttons">
        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?= $pagina - 1 ?>" class="pagination-button">Anterior</a>
        <?php endif; ?>
        
        <?php
        $inicio = max(1, $pagina - 2);
        $fin = min($totalPaginas, $pagina + 2);
        
        for ($i = $inicio; $i <= $fin; $i++):
        ?>
            <a href="?pagina=<?= $i ?>" 
               class="pagination-button <?= $i === $pagina ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($pagina < $totalPaginas): ?>
            <a href="?pagina=<?= $pagina + 1 ?>" class="pagination-button">Siguiente</a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modalUsuarioBody">
      <!-- Aquí se cargará el formulario por AJAX -->
    </div>
  </div>
</div>

<!-- Asegúrate de que jQuery y Bootstrap JS estén cargados antes de este script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function() {
    // Cambiar de página
    window.cambiarPagina = function(pagina) {
        const url = new URL(window.location.href);
        url.searchParams.set('pagina', pagina);
        window.location.href = url.toString();
    }

    // Mostrar modal de usuario
    window.mostrarModalUsuario = function(id = null) {
        const url = id ? `usuarios/get?id=${id}` : 'usuarios/get';
        $('#modalUsuarioBody').load(url, function() {
            $('#modalUsuario').modal('show');
        });
    }

    // Editar usuario
    window.editarUsuario = function(id) {
        window.mostrarModalUsuario(id);
    }

    // Eliminar usuario con advertencia de asociaciones
    window.eliminarUsuario = function(id) {
        // Consultar asociaciones antes de eliminar
        $.get('usuarios/getAsociaciones?id_usuario=' + id, function(response) {
            if (typeof response === 'string') {
                try { response = JSON.parse(response); } catch (e) { response = {}; }
            }
            let msg = '¿Estás seguro de que deseas eliminar este usuario? Esta acción eliminará el usuario de forma permanente.';
            if (response.mascotas || response.dispositivos) {
                msg += '<br><br><b>Advertencia:</b> Este usuario tiene:';
                if (response.mascotas) {
                    msg += `<br>- ${response.mascotas} mascota(s) asociada(s)`;
                }
                if (response.dispositivos) {
                    msg += `<br>- ${response.dispositivos} dispositivo(s) asociado(s)`;
                }
                msg += '<br>Al eliminar el usuario, también se eliminarán todas sus mascotas y dispositivos.';
            }
            Swal.fire({
                title: 'Confirmar eliminación',
                html: msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('usuarios/eliminar/' + id, function(response) {
                        if (typeof response === 'string') {
                            try { response = JSON.parse(response); } catch (e) { response = {}; }
                        }
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: 'El usuario ha sido eliminado correctamente.'
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error || 'Error al eliminar el usuario.'
                            });
                        }
                    });
                }
            });
        });
    }

    // Cambiar estado de usuario con feedback y confirmación SweetAlert2
    $(document).on('change', '.cambiar-estado-usuario', function() {
        const id = $(this).data('id');
        const estado = $(this).prop('checked') ? 'activo' : 'inactivo';
        const $checkbox = $(this);
        $.post('usuarios/cambiarEstado/' + id, { id_usuario: id, estado: estado }, function(response) {
            // Asegurarse de que la respuesta sea un objeto
            if (typeof response === 'string') {
                try { response = JSON.parse(response); } catch (e) { response = {}; }
            }
            console.log(response); // Depuración
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Estado actualizado',
                    text: response.message || 'El estado del usuario se actualizó correctamente.'
                });
            } else if (response.needsConfirmation) {
                // Mostrar consecuencias y pedir confirmación
                let msg = response.message || 'Esta acción afectará elementos asociados.';
                if (response.data && response.data.mascotas) {
                    msg += `\n\nMascotas asociadas: ${response.data.mascotas.length}`;
                }
                Swal.fire({
                    icon: 'warning',
                    title: '¿Estás seguro?',
                    html: msg.replace(/\n/g, '<br>'),
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Confirmar cambio en cascada
                        $.post('usuarios/confirmarCambioEstado', { id_usuario: id, estado: estado }, function(resp2) {
                            if (typeof resp2 === 'string') {
                                try { resp2 = JSON.parse(resp2); } catch (e) { resp2 = {}; }
                            }
                            if (resp2.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Estado actualizado',
                                    text: resp2.message || 'El estado del usuario y sus asociados se actualizó correctamente.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp2.error || 'Error al cambiar el estado en cascada.'
                                });
                                $checkbox.prop('checked', !($checkbox.prop('checked'))); // Revertir cambio
                            }
                        });
                    } else {
                        $checkbox.prop('checked', !($checkbox.prop('checked'))); // Revertir cambio
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'Error al cambiar el estado del usuario.'
                });
                $checkbox.prop('checked', !($checkbox.prop('checked'))); // Revertir cambio
            }
        });
    });

    $('#buscar, #rol, #estado').on('input change', function() {
        aplicarFiltros();
    });
});
</script>

<?php
// Bloque JS específico para usuarios
ob_start();
?>
<script>
    // Función para actualizar la paginación según la altura de la pantalla
    function actualizarPaginacion() {
        const alturaPantalla = window.innerHeight;
        const url = new URL(window.location.href);
        url.searchParams.set('altura', alturaPantalla);
        window.location.href = url.toString();
    }
    // Solo recargar al cargar la página si no hay parámetro de altura
    window.addEventListener('load', function() {
        if (!new URL(window.location.href).searchParams.get('altura')) {
            actualizarPaginacion();
        }
    });
    // Si quieres recargar al redimensionar, descomenta la siguiente línea:
    // window.addEventListener('resize', actualizarPaginacion);

    // Aplicar filtros
    function aplicarFiltros() {
        const buscar = document.getElementById('buscar').value;
        const rol = document.getElementById('rol').value;
        const estado = document.getElementById('estado').value;
        const url = new URL(window.location.href);
        url.searchParams.set('buscar', buscar);
        url.searchParams.set('rol', rol);
        url.searchParams.set('estado', estado);
        url.searchParams.set('pagina', '1');
        window.location.href = url.toString();
    }
</script>
<?php
$extra_js = ob_get_clean();
$GLOBALS['extra_js'] = $extra_js;
?>

<!-- Botón flotante para agregar usuario -->
<link rel="stylesheet" href="assets/css/boton.css">
<button class="fab-btn" onclick="mostrarModalUsuario()" aria-label="Agregar usuario">
    <span class="fab-icon">+</span>
    <span class="fab-text">Agregar usuario</span>
</button> 