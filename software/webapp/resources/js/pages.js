// Page-level bindings applied to all roles (admin and user)
(function(){
  const DISPLAY_TZ = 'America/Mexico_City';
  const TXT = {
    es: {
      selectPoint: 'Selecciona un punto',
      allPoints: 'Todos los puntos',
      customRange: 'rango de fechas',
      infoTitle: 'Información',
      noReadings: 'No se encontrarón lecturas',
      pointId: 'ID Punto',
      energy: 'Consumo eléctrico (kWh)',
      current: 'Corriente RMS (A)',
      voltage: 'Voltaje RMS (V)',
      power: 'Potencia aparente (VA)',
      date: 'Fecha',
      noData: 'Sin datos',
      consumptionShort: 'Consumo (kWh)',
      currentShort: 'Corriente (A)',
      voltageShort: 'Voltaje (V)',
      powerShort: 'Potencia (W)',
      pointFallback: 'Punto',
      pointLabel: 'ID punto',
      chipLabel: 'Chip',
      noDataStatus: 'Sin datos',
      lastReading: 'Última lectura',
      active: 'Activo',
      inactive: 'Inactivo',
      statusError: 'Error',
      noPointsRealtime: 'No hay puntos disponibles para mostrar lecturas.',
      realtimeLoadError: 'No fue posible cargar las lecturas en tiempo real.',
      readError: 'No se pudo obtener lectura',
      pointsError: 'No se pudo obtener puntos',
      pointsFallbackError: 'No se pudo obtener puntos (fallback)'
    },
    en: {
      selectPoint: 'Select a point',
      allPoints: 'All points',
      customRange: 'rango de fechas',
      infoTitle: 'Information',
      noReadings: 'No readings found',
      pointId: 'Point ID',
      energy: 'Energy consumption (kWh)',
      current: 'Current RMS (A)',
      voltage: 'Voltage RMS (V)',
      power: 'Apparent power (VA)',
      date: 'Timestamp',
      noData: 'No data',
      consumptionShort: 'Consumption (kWh)',
      currentShort: 'Current (A)',
      voltageShort: 'Voltage (V)',
      powerShort: 'Power (W)',
      pointFallback: 'Point',
      pointLabel: 'Point ID',
      chipLabel: 'Chip',
      noDataStatus: 'No data',
      lastReading: 'Last reading',
      active: 'Active',
      inactive: 'Inactive',
      statusError: 'Error',
      noPointsRealtime: 'No points available to show readings.',
      realtimeLoadError: 'Could not load real-time readings.',
      readError: 'Could not fetch reading',
      pointsError: 'Could not fetch points',
      pointsFallbackError: 'Could not fetch points (fallback)'
    }
  };
  function getLang(){
    const path = (window.location && window.location.pathname ? window.location.pathname : '').toLowerCase();
    if(path === '/en' || path.startsWith('/en/')) return 'en';
    if(path === '/es' || path.startsWith('/es/')) return 'es';

    const metaLang = ((document.querySelector('meta[name="app-lang"]')?.getAttribute('content')) || '').toLowerCase();
    if(metaLang === 'en' || metaLang === 'es') return metaLang;

    const htmlLang = ((document.documentElement && document.documentElement.lang) || '').toLowerCase();
    if(htmlLang.startsWith('en')) return 'en';
    if(htmlLang.startsWith('es')) return 'es';

    return 'es';
  }

  function t(key){
    const locale = getLang();
    return (TXT[locale] && TXT[locale][key]) || TXT.es[key] || key;
  }
  function qs(sel){ return document.querySelector(sel); }
  function readMeta(name){
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.getAttribute('content') : '';
  }
  function getUserContext(){
    const uidFromMeta = readMeta('app-uid') || '';
    const tipoFromMeta = readMeta('app-tipo') || '';
    const hasApp = (typeof app !== 'undefined' && app && app.user);
    return {
      uid: hasApp ? (app.user.id || uidFromMeta) : uidFromMeta,
      tipo: hasApp ? (app.user.tipo || tipoFromMeta) : tipoFromMeta
    };
  }
  function onReady(fn){
    if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', fn); }
    else { fn(); }
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
    const locale = getLang() === 'en' ? 'en-US' : 'es-MX';
    return new Intl.DateTimeFormat(locale, {
      timeZone: DISPLAY_TZ,
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    }).format(d);
  }

  function bindTiempoReal(){
    const page = qs('#tiempo-real');
    if(!page) return;
    const sel = qs('#selPoint');
    const btn = qs('#btnVer');
    if(!sel || !btn) return;

    // Load user's points into selector
    const ctx = getUserContext();
    const uid = ctx.uid || '';
    fetch('/app/app.php?_lup&uid=' + encodeURIComponent(uid))
      .then(r => r.json())
      .then(points => {
        const opts = [`<option value="">${t('selectPoint')}</option>`]
          .concat(points.map(p => `<option value="${p.id}">${p.name}</option>`));
        sel.innerHTML = opts.join('');
      })
      .catch(() => {});

    btn.addEventListener('click', () => {
      const pid = sel.value;
      if(!pid) return;
      if(window.DASH_API){
        window.DASH_API.setPoint(pid);
        window.DASH_API.refresh();
      }
      // Quick probe: if no readings found, show info modal
      const params = new URLSearchParams({ _readings: '1', pointId: pid, metrics: 'voltaje_RMS', limit: '1' });
      fetch('/app/app.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
          const has = Array.isArray(data.timestamps) && data.timestamps.length > 0;
          if(!has && window.appModal){
            window.appModal.show({ title: t('infoTitle'), message: t('noReadings'), type: 'info' });
          }
        })
        .catch(() => {});
    });
  }

  function bindHistorico(){
    const page = qs('#historico');
    if(!page) return;
    const selPoint = qs('#selPointH');
    const selRange = qs('#selRange');
    const f1 = qs('#fechaInicio');
    const f2 = qs('#fechaFin');
    const cont = qs('#tablaHistorico');
    const btnBuscar = qs('#btnBuscar');
    if(!selPoint || !selRange || !f1 || !f2 || !cont || !btnBuscar) return;

    // Populate points
    const ctx = getUserContext();
    const uid = ctx.uid || '';
    fetch('/app/app.php?_lup&uid=' + encodeURIComponent(uid))
      .then(r => r.json())
      .then(points => {
        const opts = [`<option value="">${t('allPoints')}</option>`]
          .concat(points.map(p => `<option value="${p.id}">${p.name}</option>`));
        selPoint.innerHTML = opts.join('');
      })
      .catch(() => {});

    // Toggle date inputs for custom range
    selRange.addEventListener('change', () => {
      const v = selRange.value.toLowerCase();
      const isCustom = (v === t('customRange'));
      f1.classList.toggle('d-none', !isCustom);
      f2.classList.toggle('d-none', !isCustom);
    });

    function fetchStatsAndTable(){
      const pid = selPoint.value || '';
      const range = selRange.value;
      const params = new URLSearchParams({ _hist_stats: '1', range });
      const paramsList = new URLSearchParams({ _hist: '1', range });
      if(pid) { params.set('pointId', pid); paramsList.set('pointId', pid); }
      if(range.toLowerCase() === t('customRange')){
        if(f1.value) { params.set('start', f1.value); paramsList.set('start', f1.value); }
        if(f2.value) { params.set('end', f2.value); paramsList.set('end', f2.value); }
      }
      // KPIs
      fetch('/app/app.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
          const avg = data.avg || {};
          document.getElementById('kpi-consumo').textContent = Number(avg.consumo_electrico ?? 0).toFixed(2);
          document.getElementById('kpi-corriente').textContent = Number(avg.corriente_RMS ?? 0).toFixed(2);
          document.getElementById('kpi-voltaje').textContent = Number(avg.voltaje_RMS ?? 0).toFixed(2);
          document.getElementById('kpi-potencia').textContent = Number(avg.potencia_aparente ?? 0).toFixed(2);
        })
        .catch(() => {});

      // Table
      fetch('/app/app.php?' + paramsList.toString())
        .then(r => r.json())
        .then(rows => {
          let html = `
            <div class="table-responsive">
              <table class="table border table-hover table-points-grid align-middle table-sm">
                <thead class="table-light">
                  <tr>
                    <th>${t('pointId')}</th>
                    <th>${t('energy')}</th>
                    <th>${t('current')}</th>
                    <th>${t('voltage')}</th>
                    <th>${t('power')}</th>
                    <th>${t('date')}</th>
                  </tr>
                </thead>
                <tbody>`;
          if(!rows || rows.length === 0){
            html += `<tr><td colspan="6" class="text-center">${t('noData')}</td></tr>`;
            if(window.appModal){
              window.appModal.show({ title: t('infoTitle'), message: t('noReadings'), type: 'info' });
            }
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
        })
        .catch(() => {});
    }

    btnBuscar.addEventListener('click', fetchStatsAndTable);
    // Initial load
    fetchStatsAndTable();
  }

  function bindInicioRealtimeCards(){
    const page = qs('#inicio');
    const grid = qs('#inicioRealtimeCards');
    if(!page || !grid) return;
    const ACTIVE_WINDOW_MS = 5 * 60 * 1000;

    const metrics = [
      { key: 'consumo_electrico', label: t('consumptionShort') },
      { key: 'corriente_RMS', label: t('currentShort') },
      { key: 'voltaje_RMS', label: t('voltageShort') },
      { key: 'potencia_aparente', label: t('powerShort') }
    ];

    function renderEmpty(message){
      grid.innerHTML = `
        <div class="col-12">
          <div class="alert alert-secondary mb-0">${message}</div>
        </div>`;
    }

    function metricsMarkup(){
      return metrics.map(m => `
        <div class="col-6 col-lg-3">
          <div class="border rounded p-2 h-100">
            <div class="small text-muted">${m.label}</div>
            <div class="fw-semibold" data-metric-value="${m.key}">-</div>
          </div>
        </div>
      `).join('');
    }

    function renderCards(points){
      grid.innerHTML = points.map(p => `
        <div class="col-12 col-md-6 col-xl-4" data-point-card data-point-id="${p.id}">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div>
                  <h6 class="card-title mb-1">${p.name || (t('pointFallback') + ' ' + p.id)}</h6>
                  <div class="small text-muted">${t('pointLabel')}: ${p.id}${p.id_chip ? (' | ' + t('chipLabel') + ': ' + p.id_chip) : ''}</div>
                </div>
                <span class="badge text-bg-light" data-point-status>${t('noDataStatus')}</span>
              </div>
              <div class="row g-2">${metricsMarkup()}</div>
              <div class="small text-muted mt-3">${t('lastReading')}: <span data-point-ts>-</span></div>
            </div>
          </div>
        </div>
      `).join('');
    }

    function valueFromSeries(series){
      if(!Array.isArray(series) || series.length === 0) return null;
      for(let i = series.length - 1; i >= 0; i--){
        const n = Number(series[i]);
        if(Number.isFinite(n)) return n;
      }
      return null;
    }

    function fmt(n){
      const locale = getLang() === 'en' ? 'en-US' : 'es-MX';
      return n === null ? '-' : new Intl.NumberFormat(locale, { maximumFractionDigits: 2 }).format(n);
    }

    function isActiveByTimestamp(ts){
      const parsed = parseUtcTimestamp(ts);
      if(!parsed) return false;
      return (Date.now() - parsed.getTime()) <= ACTIVE_WINDOW_MS;
    }

    async function fetchLatest(pointId){
      const params = new URLSearchParams({
        _readings: '1',
        pointId: String(pointId),
        limit: '1',
        metrics: metrics.map(m => m.key).join(',')
      });
      const resp = await fetch('/app/app.php?' + params.toString());
      if(!resp.ok) throw new Error(t('readError'));
      return resp.json();
    }

    async function refreshCards(){
      const cards = Array.from(grid.querySelectorAll('[data-point-card]'));
      await Promise.all(cards.map(async (card) => {
        const pointId = card.getAttribute('data-point-id');
        const statusEl = card.querySelector('[data-point-status]');
        const tsEl = card.querySelector('[data-point-ts]');
        if(!pointId) return;
        try {
          const data = await fetchLatest(pointId);
          const ts = Array.isArray(data.timestamps) && data.timestamps.length > 0
            ? data.timestamps[data.timestamps.length - 1]
            : null;
          const active = isActiveByTimestamp(ts);
          metrics.forEach(m => {
            const raw = data.data ? data.data[m.key] : null;
            const value = valueFromSeries(raw);
            const valueEl = card.querySelector(`[data-metric-value="${m.key}"]`);
            if(valueEl) valueEl.textContent = fmt(value);
          });
          if(statusEl){
            statusEl.textContent = active ? t('active') : t('inactive');
            statusEl.classList.toggle('text-bg-success', active);
            statusEl.classList.toggle('text-bg-secondary', !active);
            statusEl.classList.remove('text-bg-light');
          }
          if(tsEl) tsEl.textContent = formatMxDateTime(ts);
        } catch(_) {
          if(statusEl) statusEl.textContent = t('statusError');
          if(tsEl) tsEl.textContent = '-';
        }
      }));
    }

    async function fetchPointsList(){
      const ctx = getUserContext();
      const uid = ctx.uid || '';
      const isAdmin = ctx.tipo === 'Administrador general';
      const primaryUrl = isAdmin
        ? '/app/app.php?_lp=1'
        : '/app/app.php?_lup&uid=' + encodeURIComponent(uid);
      const fallbackUrl = '/app/app.php?_lp=1';

      const respPrimary = await fetch(primaryUrl);
      if(!respPrimary.ok) throw new Error(t('pointsError'));
      const primaryData = await respPrimary.json();
      if(Array.isArray(primaryData) && primaryData.length > 0){
        return primaryData;
      }
      if(isAdmin){
        return Array.isArray(primaryData) ? primaryData : [];
      }

      const respFallback = await fetch(fallbackUrl);
      if(!respFallback.ok) throw new Error(t('pointsFallbackError'));
      const fallbackData = await respFallback.json();
      return Array.isArray(fallbackData) ? fallbackData : [];
    }

    async function init(){
      try {
        const points = await fetchPointsList();
        if(!Array.isArray(points) || points.length === 0){
          renderEmpty(t('noPointsRealtime'));
          return;
        }
        renderCards(points);
        await refreshCards();
        setInterval(refreshCards, 5000);
      } catch (_) {
        renderEmpty(t('realtimeLoadError'));
      }
    }

    init();
  }

  onReady(() => {
    bindInicioRealtimeCards();
    bindTiempoReal();
    bindHistorico();
  });
})();
