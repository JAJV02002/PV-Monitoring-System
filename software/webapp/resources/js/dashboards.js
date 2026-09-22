// Generic dashboards renderer for multiple metrics
// Contract:
// - Placeholders: elements with data-metric attribute and a canvas inside
// - Config: window.DASH_CONFIG = { api: '/app/app.php?_readings', pointId, range, metrics: [{ key, label, unit, color }] }
// - Backend returns: { timestamps: [...], data: { [metricKey]: number[] } }

(function(){
  const DISPLAY_TZ = 'America/Mexico_City';
  const DFLT_COLORS = [
    'rgba(54, 162, 235, 1)',
    'rgba(255, 99, 132, 1)',
    'rgba(255, 206, 86, 1)',
    'rgba(75, 192, 192, 1)'
  ];

  function qs(sel, root=document){ return root.querySelector(sel); }
  function qsa(sel, root=document){ return Array.from(root.querySelectorAll(sel)); }

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

  function formatMxTime(ts){
    const d = parseUtcTimestamp(ts);
    if(!d) return '-';
    return new Intl.DateTimeFormat('es-MX', {
      timeZone: DISPLAY_TZ,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    }).format(d);
  }

  function createChart(ctx, label, color){
    return new Chart(ctx, {
      type: 'line',
      data: { labels: [], datasets: [{ label, data: [], borderColor: color, borderWidth: 2, tension: 0.2, pointRadius: 0 }] },
      options: { responsive: true, animation: false, scales: { x: { display: true }, y: { display: true } }, plugins: { legend: { display: true } } }
    });
  }

  function mapMetricToKey(metric){
    // normalize possible names -> backend keys
    const m = metric.toLowerCase();
    if(m.includes('volt')) return 'voltaje_RMS';
    if(m.includes('corr') || m.includes('current')) return 'corriente_RMS';
    if(m.includes('pot') || m.includes('power')) return 'potencia_aparente';
    if(m.includes('cons') || m.includes('energy') || m.includes('kwh')) return 'consumo_electrico';
    return metric; // already a key
  }

  async function fetchReadings(url){
    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if(!resp.ok) throw new Error('Readings fetch failed: ' + resp.status);
    return resp.json();
  }

  function buildURL(cfg){
    const u = new URL(cfg.api, window.location.origin);
    if(cfg.pointId) u.searchParams.set('pointId', cfg.pointId);
    if(cfg.range) u.searchParams.set('range', cfg.range);
    if(cfg.limit) u.searchParams.set('limit', cfg.limit);
    if(cfg.metrics && cfg.metrics.length){
      u.searchParams.set('metrics', cfg.metrics.map(m => m.key || mapMetricToKey(m.label || m)).join(','));
    }
    u.searchParams.set('_readings', '1');
    return u.toString();
  }

  function updateChart(chart, labels, series){
    chart.data.labels = labels;
    chart.data.datasets[0].data = series;
    chart.update();
  }

  function getLatestNumericValue(series){
    if(!Array.isArray(series)) return null;
    for(let i = series.length - 1; i >= 0; i--){
      const value = Number(series[i]);
      if(Number.isFinite(value)) return value;
    }
    return null;
  }

  function formatCurrentValue(value){
    if(value === null) return '-';
    return new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 }).format(value);
  }

  let DASH_STATE = { charts: [], cfg: null, running: false };

  function getConfigFromDOM(){
    const container = document.querySelector('#dashboards[data-dash-config]') || document.querySelector('[data-dash-config]');
    if(!container) return null;
    const raw = container.getAttribute('data-dash-config');
    try { return JSON.parse(raw); } catch(e){ console.warn('Invalid data-dash-config JSON', e); return null; }
  }

  async function init(){
    const placeholders = qsa('[data-metric]');
    if(placeholders.length === 0) return;

  const cfg = window.DASH_CONFIG || getConfigFromDOM() || {};
  if(!cfg.api) cfg.api = '/app/app.php';
  DASH_STATE.cfg = cfg;

    // Prepare charts per placeholder
  const charts = placeholders.map((el, idx) => {
      const canvas = qs('canvas', el) || el;
      const ctx = canvas.getContext('2d');
      const label = el.dataset.label || el.dataset.metric;
      const color = el.dataset.color || (cfg.metrics && cfg.metrics[idx] && cfg.metrics[idx].color) || DFLT_COLORS[idx % DFLT_COLORS.length];
      const valueEl = qs('[data-current-value]', el.closest('.card-body') || el.parentElement || document);
      const chart = createChart(ctx, label, color);
      return { el, chart, key: el.dataset.key || mapMetricToKey(el.dataset.metric), unit: el.dataset.unit || '', valueEl };
    });

    async function refresh(){
      try {
        const url = buildURL({
          api: cfg.api,
          pointId: cfg.pointId,
          range: cfg.range || 'latest',
          limit: cfg.limit || 50,
          metrics: charts.map(c => ({ key: c.key }))
        });
        const data = await fetchReadings(url);
        const labels = (data.timestamps || []).map(ts => formatMxTime(ts));
        charts.forEach(c => {
          const series = (data.data && data.data[c.key]) ? data.data[c.key] : [];
          updateChart(c.chart, labels, series);
          if(c.valueEl){
            c.valueEl.textContent = formatCurrentValue(getLatestNumericValue(series));
          }
        });
      } catch(err){
        console.error('Dashboard refresh error', err);
      }
    }

    // Initial and periodic refresh
    await refresh();
    if(cfg.pollMs && cfg.pollMs > 0){
      setInterval(refresh, cfg.pollMs);
    }

    // Expose minimal API
    window.DASH_API = {
      refresh,
      setConfig: (partial) => { Object.assign(cfg, partial); },
      setPoint: (pid) => { cfg.pointId = pid; }
    };
    DASH_STATE.charts = charts;
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
