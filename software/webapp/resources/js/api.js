// Minimal fetch helper that adds CSRF token and handles auth redirects
(function(){
  function getCSRF(){
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : '';
  }
  function isJSON(r){
    const ct = r.headers.get('content-type') || '';
    return ct.includes('application/json');
  }
  window.apiFetch = function(url, options){
    const token = getCSRF();
    const opts = Object.assign({ credentials: 'same-origin' }, options || {});
    opts.headers = Object.assign({ 'X-CSRF-Token': token, 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
    return fetch(url, opts).then(async r => {
      if(r.status === 401){ window.location.href = '/login'; return Promise.reject(new Error('Unauthorized')); }
      if(!r.ok){ const msg = isJSON(r) ? JSON.stringify(await r.json()) : await r.text(); return Promise.reject(new Error(msg || 'Request failed')); }
      return r;
    });
  }
})();
