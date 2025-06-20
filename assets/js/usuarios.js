document.addEventListener('DOMContentLoaded', function() {
    // --- CONFIGURACIÓN Y VARIABLES ---
    const configElement = document.getElementById('usuarios-config');
    const APP_URL = configElement.dataset.appUrl;
    const PERMISOS = {
        editar: configElement.dataset.permisoEditar === 'true',
        eliminar: configElement.dataset.permisoEliminar === 'true'
    };

    // --- FUNCIONES ---
    
    /**
     * Calcula dinámicamente el número de filas a mostrar en la tabla
     * para que ocupe el alto de la pantalla sin generar scroll.
     * @returns {number} El número de filas a mostrar.
     */
    function getDynamicPageLength() {
        const tableWrapper = document.querySelector('.table-responsive');
        if (!tableWrapper) return 10;

        const topOffset = tableWrapper.getBoundingClientRect().top;
        const headerHeight = 56;
        const footerHeight = 50;
        const safetyMargin = 20;

        const availableHeight = window.innerHeight - topOffset - headerHeight - footerHeight - safetyMargin;
        const avgRowHeight = 48;
        const numRows = Math.floor(availableHeight / avgRowHeight);
        
        return Math.max(5, numRows);
    }

    // --- INICIALIZACIÓN DE DATATABLES ---

    const tablaUsuarios = $('#tablaUsuarios').DataTable({
        processing: true,
        serverSide: true,
        lengthChange: false,
        dom: 'fltip',
        responsive: true,
        ajax: {
            url: `${APP_URL}/usuarios/obtenerUsuarios`,
            type: 'POST'
        },
        columnDefs: [
            { "width": "5%", "targets": 0 },   // ID
            { "width": "20%", "targets": 1 },  // Nombre
            { "width": "20%", "targets": 2 },  // Email
            { "width": "10%", "targets": 3 },  // Teléfono
            { "width": "10%", "targets": 4 },  // Rol
            { "width": "15%", "targets": 5 },  // Dirección
            { "width": "10%", "targets": 6, "className": "text-center" }, // Estado
            { "width": "10%", "targets": 7, "className": "text-center" }  // Acciones
        ],
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'email' },
            { data: 'telefono' },
            { data: 'rol' },
            { data: 'direccion' },
            { 
                data: 'estado',
                orderable: false,
                render: function(data, type, row) {
                    const isChecked = data === 'activo' ? 'checked' : '';
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input estado-switch" type="checkbox" role="switch" id="estadoSwitch_${row.id}" data-id="${row.id}" ${isChecked}>
                            <label class="form-check-label" for="estadoSwitch_${row.id}"></label>
                        </div>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    let editBtn = PERMISOS.editar ? `
                        <button class="btn btn-outline-primary btn-sm editar-usuario" data-id="${row.id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>` : '';

                    let deleteBtn = PERMISOS.eliminar ? `
                        <button class="btn btn-outline-danger btn-sm eliminar-usuario ms-1" data-id="${row.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>` : '';

                    // Si no hay permisos, no mostrar nada
                    if (!editBtn && !deleteBtn) {
                        return '<span class="text-muted small">Sin acciones</span>';
                    }

                    // Botones visibles en pantallas medianas y grandes
                    const buttonGroup = `
                        <div class="btn-group d-none d-md-inline-flex" role="group">
                            ${editBtn}
                            ${deleteBtn}
                        </div>`;

                    // Dropdown visible solo en pantallas pequeñas
                    const dropdown = `
                        <div class="dropdown d-md-none">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                ${PERMISOS.editar ? `<li><a class="dropdown-item editar-usuario" href="#" data-id="${row.id}">Editar</a></li>` : ''}
                                ${PERMISOS.eliminar ? `<li><a class="dropdown-item eliminar-usuario" href="#" data-id="${row.id}">Eliminar</a></li>` : ''}
                            </ul>
                        </div>`;

                    return buttonGroup + dropdown;
                }
            }
        ],
        language: {
            url: `${APP_URL}/assets/js/i18n/Spanish.json`
        },
        initComplete: function() {
            const newPageLength = getDynamicPageLength();
            this.api().page.len(newPageLength).draw();
        }
    });

    // --- MANEJO DE EVENTOS ---

    // Ajustar la tabla al redimensionar la ventana
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const newPageLength = getDynamicPageLength();
            tablaUsuarios.page.len(newPageLength).draw();
        }, 250);
    });

    // Cambiar estado de usuario con switch
    $('#tablaUsuarios tbody').on('change', '.estado-switch', function() {
        const userId = $(this).data('id');
        const nuevoEstado = this.checked ? 'activo' : 'inactivo';

        fetch(`${APP_URL}/usuarios/toggleEstado`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${userId}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Éxito', text: data.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                $(this).prop('checked', !this.checked); // Revertir si falla
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación.' });
            $(this).prop('checked', !this.checked);
        });
    });

    // --- GESTIÓN DE MODALES (Crear y Editar) ---

    const modalElement = document.getElementById('modal-generico');
    const modal = new bootstrap.Modal(modalElement);
    const modalContent = modalElement.querySelector('.modal-content');

    // Cargar formulario para NUEVO usuario
    document.getElementById('btnNuevoUsuarioFlotante').addEventListener('click', function() {
        fetch(`${APP_URL}/usuarios/cargarFormulario`)
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
                modal.show();
            });
    });

    // Cargar formulario para EDITAR usuario
    $('#tablaUsuarios tbody').on('click', '.editar-usuario', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        fetch(`${APP_URL}/usuarios/cargarFormulario/${id}`)
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
                modal.show();
            });
    });

    // Enviar formulario de crear/editar
    modalElement.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'formUsuario') {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const url = form.action;

            fetch(url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        tablaUsuarios.ajax.reload(); // Recargar la tabla
                        Swal.fire({ icon: 'success', title: 'Éxito', text: data.message, timer: 2000, showConfirmButton: false});
                    } else {
                        // Construir un mensaje de error detallado
                        let errorHtml = '';
                        if (data.errors) {
                            // Si hay un objeto de errores, los formateamos como una lista
                            errorHtml = '<ul class="text-start">';
                            for (const key in data.errors) {
                                errorHtml += `<li>${data.errors[key]}</li>`;
                            }
                            errorHtml += '</ul>';
                        } else {
                            // Mensaje genérico si no hay un objeto de errores
                            errorHtml = data.message || 'Ocurrió un error inesperado.';
                        }
                        
                        if (data.error_code) {
                            errorHtml += `<br><small>Código: <strong>${data.error_code}</strong></small>`;
                        }

                        if (data.details) {
                             errorHtml += `<br><strong>Detalles:</strong><pre style="text-align: left; font-size: 0.8em; white-space: pre-wrap;">${data.details}</pre>`;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Validación',
                            html: errorHtml
                        });
                    }
                })
                .catch(() => Swal.fire('Error', 'No se pudo procesar la solicitud. Verifique su conexión o contacte al administrador.', 'error'));
        }
    });

    // ELIMINAR usuario
    $('#tablaUsuarios tbody').on('click', '.eliminar-usuario', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${APP_URL}/usuarios/eliminarUsuario/${id}`, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            tablaUsuarios.ajax.reload();
                            Swal.fire('Eliminado', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error'));
            }
        });
    });
}); 