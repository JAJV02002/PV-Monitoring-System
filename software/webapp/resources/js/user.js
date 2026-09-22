const appUser = {
    tablePts : $("#tablePts"),
  pointsData : [],

  lang : function(){
    return (document.querySelector('meta[name="app-lang"]')?.getAttribute('content') || 'es').toLowerCase();
  },

  t : function(key){
    const dict = {
      es: {
        active: 'Activo',
        inactive: 'Inactivo',
        noPoints: 'Aún no hay puntos registrados',
        assigned: 'Asignado',
        edit: 'Modificar',
        delete: 'Eliminar',
        loading: 'Cargando puntos...'
      },
      en: {
        active: 'Active',
        inactive: 'Inactive',
        noPoints: 'No points registered yet',
        assigned: 'Assigned',
        edit: 'Edit',
        delete: 'Delete',
        loading: 'Loading points...'
      }
    };
    const locale = this.lang();
    return (dict[locale] && dict[locale][key]) || dict.es[key] || key;
  },

  statusBadge : function(status){
    const raw = String(status ?? '').toLowerCase().trim();
    const isActive = raw === '1' || raw === 'true' || raw === 'activo' || raw === 'online';
    const cls = isActive ? 'text-bg-success' : 'text-bg-secondary';
    const label = isActive ? this.t('active') : this.t('inactive');
    return `<span class="badge ${cls}">${label}</span>`;
  },

  isPointActive : function(point){
    const raw = String((point && point.status) ?? '').toLowerCase().trim();
    return raw === '1' || raw === 'true' || raw === 'activo' || raw === 'online';
  },

  renderPointsRows : function(points){
    if(!Array.isArray(points) || points.length === 0){
      this.tablePts.html(`<tr><td colspan="5" class="text-center">${this.t('noPoints')}</td></tr>`);
      return;
    }

    const lang = (document.querySelector('meta[name="app-lang"]')?.getAttribute('content') || 'es').toLowerCase();
    const localePrefix = lang === 'en' ? '/en' : '';

    const html = points.map((point) => `
      <tr>
          <td> <a href="${localePrefix}/puntos/punto">${point.name}</a> </td>
          <td>${point.id_chip || '-'}</td>
          <td>${this.t('assigned')}</td>
          <td>${this.statusBadge(point.status)}</td>
          <td class="w-25 text-center"> 
            <button class="btn btn-info">${this.t('edit')}</button> 
            <button class="btn btn-danger" data-action="del" data-pid="${point.id}">${this.t('delete')}</button> 
          </td>
      </tr>
    `).join('');

    this.tablePts.html(html);
  },

  applyStatusFilter : function(){
    const filterEl = document.getElementById('pointsStatusFilter');
    const mode = filterEl ? filterEl.value : 'all';
    let rows = Array.isArray(this.pointsData) ? [...this.pointsData] : [];

    if(mode === 'active'){
      rows = rows.filter((p) => this.isPointActive(p));
    } else if(mode === 'inactive'){
      rows = rows.filter((p) => !this.isPointActive(p));
    }

    this.renderPointsRows(rows);
    this.bindDeleteHandlers();
  },

  bindDeleteHandlers : function(){
    const token = document.querySelector('meta[name="csrf-token"]').content;
    this.tablePts.find('button[data-action="del"]').on('click', (e) => {
      const pid = e.currentTarget.getAttribute('data-pid');
      if(!pid) return;
      const body = new URLSearchParams({ _dp: '1', pid, _csrf: token }).toString();
      apiFetch(app.routes.app, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
        .then(r => r.json())
        .then(r => { if(r && r.r){ this.getPoints(); } });
    });
  },

    getPoints : function(){
        if (!this.tablePts || !this.tablePts.length) {
          return;
        }
        // console.info('Mis puntos');
            this.tablePts.html(`<tr><td colspan="5" class="text-center">${this.t('loading')}</td></tr>`);
        //lup = load user point
  apiFetch(app.routes.app + "?_lup&uid=" + app.user.id)
              .then(resp => resp.json())
              .then(points => {
                this.pointsData = Array.isArray(points) ? points : [];
                this.applyStatusFilter();
              })
              .catch(() => {
                this.pointsData = [];
                this.renderPointsRows([]);
              });
      }
}

function parseUtcTimestamp(ts){
  if(!ts || typeof ts !== 'string') return null;
  const normalized = ts.includes('T') ? ts : ts.replace(' ', 'T');
  const hasZone = /([zZ]|[+-]\d{2}:\d{2})$/.test(normalized);
  const iso = hasZone ? normalized : (normalized + 'Z');
  const parsed = new Date(iso);
  if(!Number.isNaN(parsed.getTime())) return parsed;
  const fallback = new Date(ts);
  return Number.isNaN(fallback.getTime()) ? null : fallback;
}

function formatMxDateTime(ts){
  const d = parseUtcTimestamp(ts);
  if(!d) return '-';
  return new Intl.DateTimeFormat('es-MX', {
    timeZone: 'America/Mexico_City',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  }).format(d);
}

document.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('pointsStatusFilter');
  if(filter){
    filter.addEventListener('change', () => {
      try { appUser.applyStatusFilter(); } catch(e){}
    });
  }
});

// Page-level handlers moved to pages.js (loaded for all roles)

// Page-specific: historico interactions (no inline JS)
document.addEventListener('DOMContentLoaded', () => {
  const page = document.getElementById('historico');
  if(!page) return;
  const selPoint = document.getElementById('selPointH');
  const selRange = document.getElementById('selRange');
  const f1 = document.getElementById('fechaInicio');
  const f2 = document.getElementById('fechaFin');
  const cont = document.getElementById('tablaHistorico');

  // Populate points
  apiFetch(app.routes.app + "?_lup&uid=" + (app.user && app.user.id ? app.user.id : ''))
    .then(r => r.json())
    .then(points => {
      const opts = ['<option value="">Todos los puntos</option>']
        .concat(points.map(p => `<option value="${p.id}">${p.name}</option>`));
      selPoint.innerHTML = opts.join('');
    })
    .catch(() => {});

  selRange.addEventListener('change', () => {
    const v = selRange.value.toLowerCase();
    const isCustom = (v === 'rango de fechas');
    f1.classList.toggle('d-none', !isCustom);
    f2.classList.toggle('d-none', !isCustom);
  });

  function fetchStatsAndTable(){
    const pid = selPoint.value || '';
    const range = selRange.value;
    const params = new URLSearchParams({ _hist_stats: '1', range });
    const paramsList = new URLSearchParams({ _hist: '1', range });
    if(pid) { params.set('pointId', pid); paramsList.set('pointId', pid); }
    if(range.toLowerCase() === 'rango de fechas'){
      if(f1.value) { params.set('start', f1.value); paramsList.set('start', f1.value); }
      if(f2.value) { params.set('end', f2.value); paramsList.set('end', f2.value); }
    }
    // KPIs
    apiFetch(app.routes.app + '?' + params.toString())
      .then(r => r.json()).then(data => {
        const avg = data.avg || {};
        document.getElementById('kpi-consumo').textContent = ((avg.consumo_electrico ?? 0)).toFixed(2);
        document.getElementById('kpi-corriente').textContent = ((avg.corriente_RMS ?? 0)).toFixed(2);
        document.getElementById('kpi-voltaje').textContent = ((avg.voltaje_RMS ?? 0)).toFixed(2);
        document.getElementById('kpi-potencia').textContent = ((avg.potencia_aparente ?? 0)).toFixed(2);
      });
    // Table
    apiFetch(app.routes.app + '?' + paramsList.toString())
      .then(r => r.json()).then(rows => {
        let html = `
        <div class="table-responsive">
          <table class="table border table-hover table-points-grid align-middle table-sm">
            <thead class="table-light">
                    <tr>
                        <th>ID Punto</th>
                        <th>Consumo eléctrico (kWh)</th>
                        <th>Corriente RMS (A)</th>
                        <th>Voltaje RMS (V)</th>
                        <th>Potencia aparente (VA)</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>`;
        if(!rows || rows.length === 0){
          html += '<tr><td colspan="6" class="text-center">Sin datos</td></tr>';
        } else {
          rows.forEach(r => {
            html += `<tr>
                <td>${(r.id_punto||'-')}</td>
                <td>${(r.consumo_electrico||'-')}</td>
                <td>${(r.corriente_RMS||'-')}</td>
                <td>${(r.voltaje_RMS||'-')}</td>
                <td>${(r.potencia_aparente||'-')}</td>
                <td>${formatMxDateTime(r.fecha_captura)}</td>
            </tr>`;
          });
        }
        html += '</tbody></table></div>';
        cont.innerHTML = html;
      });
  }

  document.getElementById('btnBuscar').addEventListener('click', fetchStatsAndTable);
  // Initial load
  fetchStatsAndTable();
});