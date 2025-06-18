// JS para el reporte moderno de monitoreo IoT de mascotas
$(function() {
  // Inicializar Select2 para propietario
  $('#propietario').select2({
    ajax: {
      url: BASE_URL + 'reporte/getPropietarios',
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => data
    },
    placeholder: 'Buscar dueño...',
    allowClear: true,
    width: '100%'
  });

  // Mascota dependiente del propietario
  $('#propietario').on('change', function() {
    const usuario_id = $(this).val();
    if (!usuario_id) {
      $('#mascota').prop('disabled', true).val(null).trigger('change');
      return;
    }
    $('#mascota').prop('disabled', false).val(null).trigger('change');
    $('#mascota').select2({
      ajax: {
        url: BASE_URL + 'reporte/getMascotasPorPropietario',
        dataType: 'json',
        delay: 250,
        data: params => ({ usuario_id, q: params.term }),
        processResults: data => data
      },
      placeholder: 'Selecciona mascota...',
      allowClear: true,
      width: '100%'
    });
  });

  // MAC con autocompletado
  $('#mac').select2({
    ajax: {
      url: BASE_URL + 'reporte/getMacs',
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => data
    },
    tags: true,
    placeholder: 'Buscar MAC...',
    allowClear: true,
    width: '100%',
    minimumInputLength: 2
  });

  // Mostrar todas
  $('#mostrar-todo').on('click', function() {
    $('#propietario').val(null).trigger('change');
    $('#mascota').val(null).trigger('change').prop('disabled', true);
    $('#mac').val(null).trigger('change');
    cargarRegistros();
  });

  // Cargar tabla al cambiar filtros
  $('#propietario, #mascota, #mac').on('change', function() {
    cargarRegistros();
  });

  // Inicializar mapa
  let map = L.map('map').setView([19.4326, -99.1332], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);
  let marker = null;

  // Cargar registros y actualizar tabla/mapa
  function cargarRegistros(page = 1) {
    const usuario_id = $('#propietario').val();
    const mascota_id = $('#mascota').val();
    const mac = $('#mac').val();
    $.getJSON(BASE_URL + 'reporte/getRegistros', {
      usuario_id, mascota_id, mac, page, perPage: 20
    }, function(resp) {
      let html = '';
      resp.data.forEach((r, i) => {
        let tempClass = r.temperatura > 39 ? 'text-danger fw-bold' : 'text-primary';
        let bpmClass = r.ritmo_cardiaco > 180 ? 'text-danger fw-bold' : 'text-success';
        let batClass = r.bateria < 20 ? 'text-warning fw-bold' : 'text-success';
        html += `<tr data-lat="${r.latitud}" data-lng="${r.longitud}">
          <td>${r.fecha_hora}</td>
          <td class="${tempClass}">${r.temperatura ?? '-'}</td>
          <td class="${bpmClass}">${r.ritmo_cardiaco ?? '-'}</td>
          <td>${r.ubicacion || '-'}</td>
          <td class="${batClass}">${r.bateria ?? '-'}</td>
        </tr>`;
      });
      $('#tabla-registros tbody').html(html);
      // Paginador
      let totalPages = Math.ceil(resp.total / resp.perPage);
      let pagHtml = '';
      for (let i = 1; i <= totalPages; i++) {
        pagHtml += `<button class="btn btn-sm ${i === resp.page ? 'btn-primary' : 'btn-outline-primary'} mx-1" onclick="cargarRegistros(${i})">${i}</button>`;
      }
      $('#paginador').html(pagHtml);
      // Mapa: centrar en el primer registro
      if (resp.data.length && resp.data[0].latitud && resp.data[0].longitud) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([resp.data[0].latitud, resp.data[0].longitud]).addTo(map);
        map.setView([resp.data[0].latitud, resp.data[0].longitud], 15);
      }
    });
  }
  window.cargarRegistros = cargarRegistros;
  cargarRegistros();

  // Al hacer click en una fila, centrar mapa
  $('#tabla-registros').on('click', 'tr', function() {
    let lat = $(this).data('lat');
    let lng = $(this).data('lng');
    if (lat && lng) {
      if (marker) map.removeLayer(marker);
      marker = L.marker([lat, lng]).addTo(map);
      map.setView([lat, lng], 16);
    }
  });

  // Exportar a Excel
  $('#exportar-excel').on('click', function() {
    const usuario_id = $('#propietario').val();
    const mascota_id = $('#mascota').val();
    const mac = $('#mac').val();
    let url = BASE_URL + 'reporte/exportarExcel?usuario_id=' + (usuario_id||'') + '&mascota_id=' + (mascota_id||'') + '&mac=' + (mac||'');
    window.open(url, '_blank');
  });
}); 