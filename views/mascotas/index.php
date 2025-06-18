<?php
// Test: Esto es un comentario de prueba
require_once 'views/layouts/header.php';
?>

<div class="container-fluid dashboard-compact" style="flex:1 1 auto;display:flex;flex-direction:column;min-height:0;">
    <div class="row" style="flex:1 1 auto;display:flex;flex-direction:column;min-height:0;">
        <div class="col-12" style="flex:1 1 auto;display:flex;flex-direction:column;min-height:0;">
            <div class="card" style="flex:1 1 auto;display:flex;flex-direction:column;min-height:0;">
                <div class="card-body" style="flex:1 1 auto;display:flex;flex-direction:column;min-height:0;">
                    <div class="table-responsive" style="flex:1 1 auto;min-height:0;overflow-y:auto;">
                        <table id="tablaMascotas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Especie</th>
                                    <th>Tamaño</th>
                                    <th>Género</th>
                                    <th>Propietario</th>
                                    <th>Edad</th>
                                    <th>Estado</th>
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
    </div>
</div>

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

<!-- FAB: Botón flotante de acción principal para la página de mascotas -->
<button class="fab-btn" id="btnNuevaMascotaFlotante" data-bs-toggle="modal" data-bs-target="#modalMascota" aria-label="Nueva Mascota">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Nueva Mascota</span>
</button>

<?php require_once 'views/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = "<?= BASE_URL ?>";

    function calcularPageLength() {
        // Altura disponible para la tabla
        const windowHeight = window.innerHeight;
        // Altura aproximada de header, paddings, etc.
        const headerHeight = 320; // Ajusta este valor según tu layout
        const rowHeight = 48; // Altura promedio de una fila de la tabla
        const available = windowHeight - headerHeight;
        const filas = Math.max(5, Math.floor(available / rowHeight));
        return filas;
    }

    let tablaMascotas = $('#tablaMascotas').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10, // Igual que usuarios
        lengthChange: false,
        dom: 'fltip',
        responsive: true,
        ajax: {
            url: BASE_URL + 'mascotas/tabla',
            type: 'POST',
            dataSrc: function(json) {
                return json.data || [];
            }
        },
        columns: [
            { data: 'id_mascota' },
            { data: 'nombre' },
            { data: 'especie' },
            { data: 'tamano', defaultContent: '-' },
            { data: 'genero', defaultContent: '-' },
            { data: 'propietario_nombre', defaultContent: 'Sin propietario' },
            {
                data: 'fecha_nacimiento',
                render: function(data) {
                    if (data) {
                        const nacimiento = new Date(data);
                        const hoy = new Date();
                        let edad = hoy.getFullYear() - nacimiento.getFullYear();
                        const m = hoy.getMonth() - nacimiento.getMonth();
                        if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
                            edad--;
                        }
                        return edad + ' año' + (edad !== 1 ? 's' : '');
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: 'estado',
                render: function(data, type, row) {
                    const checked = data === 'activo' ? 'checked' : '';
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input estado-switch" type="checkbox" role="switch" data-id="${row.id_mascota}" ${checked}>
                            <label class="form-check-label"></label>
                        </div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    let buttons = `
                        <button class="btn btn-sm btn-info editar-mascota" data-id="${data.id_mascota}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger eliminar-mascota" data-id="${data.id_mascota}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    if (data.id_dispositivo && data.id_dispositivo > 0) {
                        buttons += `
                            <a href="${BASE_URL}monitor/device/${data.id_dispositivo}" class="btn btn-sm btn-success monitor-mascota" title="Monitor">
                                <i class="fas fa-desktop"></i>
                            </a>
                        `;
                    } else {
                        buttons += `
                            <button class="btn btn-sm btn-secondary" disabled title="Sin dispositivo asociado">
                                <i class="fas fa-desktop"></i>
                            </button>
                        `;
                    }
                    return `<div class="d-flex gap-2">${buttons}</div>`;
                }
            }
        ],
        language: {
            url: '<?= APP_URL ?>/assets/js/i18n/Spanish.json'
        }
    });

    // Manejar envío del formulario
    document.getElementById('formMascota').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id_mascota = formData.get('id_mascota');
        const url = id_mascota ? `${BASE_URL}mascotas/edit/${id_mascota}` : `${BASE_URL}mascotas/create`;

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#modalMascota').modal('hide');
                tablaMascotas.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ha ocurrido un error al procesar la solicitud'
            });
        });
    });

    // Editar mascota
    document.getElementById('tablaMascotas').addEventListener('click', function(e) {
        if (e.target.closest('.editar-mascota')) {
            const id = e.target.closest('.editar-mascota').dataset.id;
            fetch(`${BASE_URL}mascotas/obtenerMascota/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('id_mascota').value = data.mascota.id_mascota;
                        document.getElementById('nombre').value = data.mascota.nombre;
                        document.getElementById('especie').value = data.mascota.especie;
                        document.getElementById('tamano').value = data.mascota.tamano;
                        document.getElementById('genero').value = data.mascota.genero;
                        // Para el propietario, necesitamos cargar el Select2 y seleccionar la opción
                        const propietarioSelect = $('#id_propietario');
                        propietarioSelect.empty().append(new Option(data.mascota.propietario_nombre, data.mascota.id_propietario, true, true)).trigger('change');
                        // Asegurarse de que Select2 se inicialice correctamente para la edición
                        initializeSelect2ForEdit(data.mascota.id_propietario, data.mascota.propietario_nombre);

                        document.getElementById('fecha_nacimiento').value = data.mascota.fecha_nacimiento;
                        $('#modalMascota').modal('show');
                    }
                });
        }

        // Eliminar mascota
        if (e.target.closest('.eliminar-mascota')) {
            const id = e.target.closest('.eliminar-mascota').dataset.id;
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}mascotas/eliminar/${id}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            tablaMascotas.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    });
                }
            });
        }
    });

    // Cambiar estado de mascota
    document.getElementById('tablaMascotas').addEventListener('change', function(e) {
        if (e.target.closest('.cambiar-estado-mascota')) {
            const checkbox = e.target.closest('.cambiar-estado-mascota');
            const id = checkbox.dataset.id;
            const nuevoEstado = checkbox.checked ? 'activo' : 'inactivo';
            const statusTextSpan = checkbox.closest('label').nextElementSibling; 

            fetch(`${BASE_URL}mascotas/cambiarEstado/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    statusTextSpan.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                    checkbox.checked = !checkbox.checked; // Revertir el cambio
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar el estado'
                });
                checkbox.checked = !checkbox.checked; // Revertir el cambio
            });
        }
    });

    // Limpiar formulario al cerrar modal
    $('#modalMascota').on('hidden.bs.modal', function() {
        document.getElementById('formMascota').reset();
        document.getElementById('id_mascota').value = '';
        // Limpiar Select2
        $('#id_propietario').val(null).trigger('change');
    });

    // Inicializar Select2 para propietarios
    function initializeSelect2() {
        if ($.fn.select2) {
            $('#id_propietario').select2({
                dropdownParent: $('#modalMascota'),
                placeholder: 'Buscar propietario...', 
                allowClear: true,
                ajax: {
                    url: BASE_URL + 'usuarios/obtenerUsuariosSelect2',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    cache: true
                }
            });
        }
    }
    initializeSelect2();

    // Función para inicializar Select2 con un valor preseleccionado para la edición
    function initializeSelect2ForEdit(id, text) {
        const selectElement = $('#id_propietario');
        if (selectElement.find("option[value='" + id + "']").length) {
            selectElement.val(id).trigger('change');
        } else {
            const newOption = new Option(text, id, true, true);
            selectElement.append(newOption).trigger('change');
        }
        selectElement.select2({
            dropdownParent: $('#modalMascota'),
            placeholder: 'Buscar propietario...', 
            allowClear: true,
            ajax: {
                url: BASE_URL + 'usuarios/obtenerUsuariosSelect2',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: (params.page * 10) < data.total_count
                        }
                    };
                },
                cache: true
            }
        });
    }

    // Forzar búsqueda automática al escribir en el input de búsqueda de DataTables (mascotas)
    $('#tablaMascotas_filter input').off().on('input', function() {
        tablaMascotas.search(this.value).draw();
    });

    // Ajustar el número de filas al redimensionar la ventana
    window.addEventListener('resize', function() {
        const newLength = calcularPageLength();
        tablaMascotas.page.len(newLength).draw(false);
    });
});
</script>