// JS para el reporte moderno de monitoreo IoT de mascotas
$(function() {
  console.log('Reportes.js cargado correctamente');
  
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
  let map = null;
  let marker = null;
  let markersGroup = null;
  const tieneMapa = $('#map').length > 0;
  if (tieneMapa) {
    map = L.map('map').setView([19.4326, -99.1332], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);
  }

  // Inicializar date range picker
  $('#rango-fechas').daterangepicker({
    autoUpdateInput: false,
    locale: {
      format: 'YYYY-MM-DD',
      separator: ' a ',
      applyLabel: 'Aplicar',
      cancelLabel: 'Limpiar',
      fromLabel: 'Desde',
      toLabel: 'Hasta',
      customRangeLabel: 'Personalizado',
      daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      firstDay: 1
    }
  });

  $('#rango-fechas').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' a ' + picker.endDate.format('YYYY-MM-DD'));
    cargarRegistros();
  });

  $('#rango-fechas').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    cargarRegistros();
  });

  // Cargar registros y actualizar tabla/mapa
  function cargarRegistros(page = 1) {
    console.log('Iniciando cargarRegistros con página:', page);
    const usuario_id = $('#propietario').val();
    const mascota_id = $('#mascota').val();
    const mac = $('#mac').val();
    const rangoFechas = $('#rango-fechas').val();
    let fecha_inicio = '', fecha_fin = '';
    if (rangoFechas && rangoFechas.includes(' a ')) {
      [fecha_inicio, fecha_fin] = rangoFechas.split(' a ');
    }
    
    console.log('Parámetros de búsqueda:', { usuario_id, mascota_id, mac, page, fecha_inicio, fecha_fin });
    
    $.getJSON(BASE_URL + 'reporte/getRegistros', {
      usuario_id, mascota_id, mac, page, perPage: 20, fecha_inicio, fecha_fin
    }, function(resp) {
      console.log('Respuesta recibida:', resp);
      let html = '';
      resp.data.forEach((r, i) => {
        let tempClass = r.temperatura > 39 ? 'text-danger fw-bold' : 'text-primary';
        let bpmClass = r.ritmo_cardiaco > 180 ? 'text-danger fw-bold' : 'text-success';
        let batClass = r.bateria < 20 ? 'text-warning fw-bold' : 'text-success';
        html += `<tr data-lat="${r.latitud}" data-lng="${r.longitud}">
          <td>${r.fecha_hora}</td>
          <td>${r.mascota_nombre || '-'}</td>
          <td>${r.dueno_nombre || '-'}</td>
          <td>${r.mac || '-'}</td>
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
    }).fail(function(xhr, status, error) {
      console.error('Error en la petición AJAX:', { xhr, status, error });
      console.log('URL de la petición:', BASE_URL + 'reporte/getRegistros');
    });
  }
  window.cargarRegistros = cargarRegistros;
  
  console.log('Llamando a cargarRegistros inicial...');
  cargarRegistros();

  // Al hacer click en una fila, centrar el mapa principal y mostrar marcador
  $('#tabla-registros').on('click', 'tr', function() {
    if (!map) return;
    const lat = $(this).data('lat');
    const lng = $(this).data('lng');
    const mascota = $(this).find('td').eq(1).text();
    const dueno = $(this).find('td').eq(2).text();
    const mac = $(this).find('td').eq(3).text();
    if (lat && lng) {
      if (marker) map.removeLayer(marker);
      marker = L.marker([lat, lng]).addTo(map);
      map.setView([lat, lng], 16);
      marker.bindPopup(`<b>${mascota}</b><br>Dueño: ${dueno}<br>MAC: <span class='text-monospace'>${mac}</span>`).openPopup();
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

  // --- NUEVO: Mostrar todas las mascotas en el mapa con popups ---
  function cargarUltimasUbicaciones() {
    if (!map) return;
    $.getJSON(BASE_URL + 'reporte/getUltimasUbicaciones', function(mascotas) {
      if (!map) return;
      if (markersGroup) map.removeLayer(markersGroup);
      markersGroup = L.layerGroup();
      mascotas.forEach(m => {
        if (m.latitude && m.longitude) {
          const marker = L.marker([m.latitude, m.longitude]).bindPopup(
            `<b>${m.mascota_nombre}</b><br>Dueño: ${m.dueno_nombre}<br>MAC: <span class='text-monospace'>${m.mac}</span><br><small>${m.fecha}</small>`
          );
          markersGroup.addLayer(marker);
        }
      });
      markersGroup.addTo(map);
      if (mascotas.length > 0) {
        const bounds = L.latLngBounds(mascotas.map(m => [m.latitude, m.longitude]));
        map.fitBounds(bounds, {padding: [30, 30]});
      }
    });
  }

  if (tieneMapa) {
    cargarUltimasUbicaciones();
    $('#propietario, #mascota, #mac').on('change', function() {
      cargarUltimasUbicaciones();
    });
  }
}); 