// Global modal helper using Bootstrap 5
(function(){
  function $(sel){ return document.querySelector(sel); }
  function setType(modalEl, type){
    const header = modalEl.querySelector('.modal-header');
    const iconMap = { info: 'text-bg-info', success: 'text-bg-success', warning: 'text-bg-warning', error: 'text-bg-danger', danger: 'text-bg-danger' };
    // reset classes
    header.classList.remove('text-bg-info','text-bg-success','text-bg-warning','text-bg-danger');
    if(iconMap[type]) header.classList.add(iconMap[type]);
  }
  window.appModal = {
    show: function(opts){
      const o = Object.assign({ title: 'Mensaje', message: '', type: 'info', closeText: 'Cerrar' }, opts||{});
      const modalEl = $('#appModal');
      if(!modalEl){ console.warn('appModal: modal element not found'); return; }
      $('#appModalLabel').textContent = o.title;
      $('#appModalBody').innerHTML = o.message; // allow simple HTML
      const btn = $('#appModalCloseBtn');
      if(btn) btn.textContent = o.closeText;
      setType(modalEl, o.type);
      // Show with Bootstrap
      const instance = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true, keyboard: true });
      instance.show();
      return instance;
    },
    hide: function(){
      const modalEl = document.getElementById('appModal');
      if(!modalEl) return;
      const instance = bootstrap.Modal.getInstance(modalEl);
      if(instance) instance.hide();
    }
  };
})();
