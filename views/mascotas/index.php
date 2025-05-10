<?php
// Permisos del usuario
$puedeCrear = true; // Permitimos que todos los usuarios puedan crear mascotas
$puedeEditar = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
$esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]); // 1: Superadmin, 2: Admin
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mis Mascotas</h1>
    </div>

    <!-- Barra de búsqueda -->
    <form id="formBuscarMascota" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" name="nombre" placeholder="Buscar por nombre...">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="especie">
                <option value="">Todas las especies</option>
                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="estado">
                <option value="">Todos los estados</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </form>

    <!-- Tabla de mascotas -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaMascotas">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Tamaño</th>
                            <?php if (in_array('gestionar_mascotas', $_SESSION['permisos'] ?? [])): ?><th>Propietario</th><?php endif; ?>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include __DIR__ . '/tabla.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Mascota -->
<div class="modal fade" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMascotaLabel">Nueva Mascota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formMascota">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="especie" class="form-label">Especie</label>
                        <select class="form-select" id="especie" name="especie" required>
                            <option value="">Seleccione una especie</option>
                            <option value="Perro">Perro</option>
                            <option value="Gato">Gato</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tamano" class="form-label">Tamaño</label>
                        <select class="form-select" id="tamano" name="tamano" required>
                            <option value="">Seleccione un tamaño</option>
                            <option value="Pequeño">Pequeño</option>
                            <option value="Mediano">Mediano</option>
                            <option value="Grande">Grande</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante para agregar mascota -->
<style>
.fab-mascota {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 999;
    background: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    padding: 0 20px 0 16px;
    height: 56px;
    min-width: 56px;
    display: flex;
    align-items: center;
    font-size: 1.3em;
    font-weight: 500;
    transition: min-width 0.4s cubic-bezier(.4,0,.2,1), padding-right 0.4s cubic-bezier(.4,0,.2,1), border-radius 0.4s cubic-bezier(.4,0,.2,1), box-shadow 0.3s;
    overflow: hidden;
    cursor: pointer;
}
.fab-mascota .fab-text {
    opacity: 0;
    width: 0;
    margin-left: 0;
    transition: opacity 0.4s cubic-bezier(.4,0,.2,1), width 0.4s cubic-bezier(.4,0,.2,1), margin-left 0.4s cubic-bezier(.4,0,.2,1);
    white-space: nowrap;
}
.fab-mascota:hover, .fab-mascota:focus {
    min-width: 180px;
    border-radius: 50px;
    padding-right: 24px;
}
.fab-mascota:hover .fab-text, .fab-mascota:focus .fab-text {
    opacity: 1;
    width: auto;
    margin-left: 12px;
}
</style>

<button class="fab-mascota" id="btnNuevaMascotaFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar Mascota</span>
</button>

<!-- Modal de edición de mascota (se llenará dinámicamente) -->
<div class="modal fade" id="modalEditarMascota" tabindex="-1" aria-labelledby="modalEditarMascotaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarMascotaLabel">Editar Mascota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="editarMascotaBody">
        <!-- Aquí se cargará el formulario de edición por AJAX -->
      </div>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Botón flotante para abrir el modal
    $('#btnNuevaMascotaFlotante').on('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('modalMascota'));
        modal.show();
    });

    // Manejar cambios en el campo especie
    $('#especie').on('change', function() {
        var especie = $(this).val();
        var $tamanoSelect = $('#tamano');
        
        if (especie === 'Gato') {
            $tamanoSelect.val('Pequeño');
            $tamanoSelect.prop('disabled', true);
        } else {
            $tamanoSelect.prop('disabled', false);
        }
    });

    // Hacer global la función para que esté disponible en scripts cargados por AJAX
    window.recargarTablaMascotas = function() {
        const formData = $('#formBuscarMascota').serialize();
        $.ajax({
            url: '/proyecto-2/mascotas/tabla?' + formData,
            type: 'GET',
            success: function(data) {
                $('#tablaMascotas tbody').html(data);
            },
            error: function() {
                Swal.fire('Error', 'No se pudo recargar la tabla de mascotas.', 'error');
            }
        });
    }

    // Manejador para el formulario de búsqueda
    $('#formBuscarMascota').on('submit', function(e) {
        e.preventDefault();
        recargarTablaMascotas();
    });

    // Manejar el envío del formulario de mascota
    $('#formMascota').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        // Calcular edad
        var fechaNacimiento = new Date($('#fecha_nacimiento').val());
        var hoy = new Date();
        var edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        
        // Ajustar edad si aún no ha cumplido años este año
        if (hoy.getMonth() < fechaNacimiento.getMonth() || 
            (hoy.getMonth() === fechaNacimiento.getMonth() && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }
        
        // Agregar edad al formData
        formData += '&edad=' + edad;

        $.ajax({
            url: '/proyecto-2/mascotas/guardar',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalMascota'));
                    if (modal) {
                        modal.hide();
                    }
                    recargarTablaMascotas();
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message || 'Mascota guardada exitosamente',
                        icon: 'success',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    $('#formMascota')[0].reset();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Error al guardar la mascota',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            },
            error: function(xhr, status, error) {
                let isHtml = xhr.responseText && xhr.responseText.trim().startsWith('<');
                let msg = isHtml
                    ? 'Tu sesión ha expirado o hubo un error inesperado. Por favor, inicia sesión nuevamente.'
                    : 'Error al guardar la mascota. Por favor, intente nuevamente.';
                Swal.fire({
                    title: 'Error',
                    text: msg,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            }
        });
    });

    // Botón editar: abrir modal de edición por AJAX
    $('#tablaMascotas').on('click', '.btnEditarMascota', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/proyecto-2/mascotas/editarModal/' + id,
            type: 'GET',
            success: function(html) {
                $('#editarMascotaBody').html(html);
                var modal = new bootstrap.Modal(document.getElementById('modalEditarMascota'));
                modal.show();
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar el formulario de edición.', 'error');
            }
        });
    });

    // Delegar eventos para los botones de eliminar (por recarga AJAX)
    $('#tablaMascotas').on('click', '.btnEliminarMascota', function() {
        var id = $(this).data('id');
        var nombreMascota = $(this).closest('tr').find('td:nth-child(2)').text();
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/proyecto-2/mascotas/delete/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            recargarTablaMascotas();
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.message || 'Mascota "' + nombreMascota + '" eliminada correctamente',
                                icon: 'success',
                                customClass: { confirmButton: 'btn btn-primary' },
                                buttonsStyling: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.error || 'Error al eliminar la mascota',
                                icon: 'error',
                                customClass: { confirmButton: 'btn btn-primary' },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function(xhr) {
                        let isHtml = xhr.responseText && xhr.responseText.trim().startsWith('<');
                        let msg = isHtml
                            ? 'Tu sesión ha expirado o hubo un error inesperado. Por favor, inicia sesión nuevamente.'
                            : 'No se pudo eliminar la mascota. Intenta de nuevo.';
                        Swal.fire({
                            title: 'Error',
                            text: msg,
                            icon: 'error',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });

    // Cambiar estado de mascota vía AJAX (ahora con switch)
    $('#tablaMascotas').on('change', '.cambiar-estado-mascota', function() {
        var id = $(this).data('id');
        var nuevoEstado = $(this).is(':checked') ? 'activo' : 'inactivo';
        $.ajax({
            url: '/proyecto-2/mascotas/cambiarEstado/' + id,
            type: 'POST',
            data: { estado: nuevoEstado },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    recargarTablaMascotas();
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: response.message || 'Estado actualizado correctamente',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.error || 'No se pudo actualizar el estado',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado. Intenta de nuevo.',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }
        });
    });
});
</script>

<?php
function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}
?> 