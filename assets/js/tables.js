/// Configuración global de DataTables
$(document).ready(function() {
    const defaultDataTableOptions = {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        paging: true,
        pageLength: 10,
        searching: true,
        lengthChange: true,
        info: true,
        ordering: true,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columnDefs: [
            {
                targets: '_all',
                orderable: true,
                className: 'align-middle'
            }
        ],
        initComplete: function() {
            $(this).find('th').addClass('sorting');
        }
    };

    // Inicializar tablas con clase 'tabla-app' o 'table' (excepto si ya son 'tabla-app')
    // La clase 'table' es un estándar de Bootstrap y es más general.
    // La clase 'tabla-app' se puede usar para configuraciones específicas si es necesario, 
    // pero por ahora, las tratamos igual.
    $('.tabla-app, .table:not(.tabla-app)').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable(defaultDataTableOptions);
        }
    });
}); 