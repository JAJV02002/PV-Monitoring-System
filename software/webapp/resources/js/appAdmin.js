const appAdmin = {
    usersTable : $("#users"),
    tablePts : $("#tablePts"),
    selectDev : $("#device_id"),
    selectUser : $("#user_id"),
    matrices : $("#grid-matrices"),
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
        selectUser: 'Selecciona un usuario',
        edit: 'Modificar',
        delete: 'Eliminar',
        centralAdmin: 'Administrador central',
        matrixAssigned: 'Matriz asignada a',
        asCentralAdmin: 'como administrador central.',
        loading: 'Cargando puntos...',
        deviceChipId: 'ChipID del dispositivo'
      },
      en: {
        active: 'Active',
        inactive: 'Inactive',
        noPoints: 'No points registered yet',
        selectUser: 'Select a user',
        edit: 'Edit',
        delete: 'Delete',
        centralAdmin: 'Central administrator',
        matrixAssigned: 'Matrix assigned to',
        asCentralAdmin: 'as central administrator.',
        loading: 'Loading points...',
        deviceChipId: 'Device Chip ID'
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
      this.tablePts.html(`<tr><td class="text-center" colspan="5">${this.t('noPoints')}</td></tr>`);
      return;
    }

    const lang = (document.querySelector('meta[name="app-lang"]')?.getAttribute('content') || 'es').toLowerCase();
    const localePrefix = lang === 'en' ? '/en' : '';

    const html = points.map((point) => `
      <tr>
          <td> <a href="${localePrefix}/puntos/punto">${point.name}</a> </td>
          <td>${point.id_chip || '-'}</td>
          <td> 
            <select class="form-select w-75" name="" id=""> 
              <option value="0">${this.t('selectUser')}</option> 
            </select> 
          </td>
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
    
  getUsers : function(){
    // console.info('Todos los usuarios');
    let html = "";
    let html1 = `<option>${this.t('centralAdmin')}</option>`;
    this.selectUser.html("");
    this.usersTable.html("");

    //lu = load users
  apiFetch(app.routes.app + "?_lu")
        .then(resp => resp.json())
        .then(users => {
          if (users.length > 0) {
              html = "";
              for (let user of users) {
                html += `
                  <tr>
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td class="w-25">
                    </td>
                    <td>
                      <a href="mailto:">${user.email}</a>
                    </td>
                    <td>${this.t('active')}</td>
                  </tr>
                `;
                html1 += `
                  <option value="${user.id}">${user.id} - ${user.name}</option>
                `;
              }
          }
          this.selectUser.html(html1);
          this.usersTable.html(html);
        }).catch((error) => {console.log(error)});

  },
  getMatrices : function(){
    let html = "Nada";
    this.matrices.html("");

    // const request1 = fetch('').then(response => response.json());
    // const request2 = fetch('').then(response => response.json());

    // Promise.all([request1, request2])
    //   .then(([data1, data2]) => {
    //     console.log(data1, data2);
    //   })
    //   .catch(error => {
    //     console.error(error);
    //   });

  apiFetch(app.routes.app + "?_lm")
        .then(resp => resp.json())
        .then(matrices => {
          if(matrices.length > 0) {
            html = "";
            for(let matriz of matrices) {
              html += `
                <div class="col-sm-4 position-relative" style="width: 33%;">
                  <button class="btn btn-bd-secondary card text-start" data-mid="${matriz.id}">
                    <div class="card-body">
                      <h5 class="card-title">${matriz.name}</h5>
                      <p class="card-text">
                        ${this.t('matrixAssigned')} <a class="link-body-emphasis" href="/perfil?id=${matriz.id_user}">${matriz.admin_central}</a> ${this.t('asCentralAdmin')}
                      </p>
                      
                      <div class="float-start">
                          <svg class="bi pe-none me-1" width="16" height="16"><use xlink:href="#device"/></svg>${matriz.tot_devices}
                          <svg class="bi pe-none ms-2 me-1" width="16" height="16"><use xlink:href="#people"/></svg>${matriz.tot_users}
                      </div>
                    </div>
                  </button>
                </div>
              `;
            }
          }
          this.matrices.html(html);
          // Bind click handlers without inline JS
          this.matrices.find('button[data-mid]').on('click', (e) => {
            const mid = e.currentTarget.getAttribute('data-mid');
            if(mid) this.getMatrix(parseInt(mid, 10));
          });
        });
    
  },
  getMatrix : function(mid){

  },
  getPoints : function(){
    this.tablePts.html(`<tr><td class="text-center" colspan="5">${this.t('loading')}</td></tr>`);
    //lp = load point
  apiFetch(app.routes.app + "?_lp")
          .then(resp => resp.json())
          .then(points => {
            this.pointsData = Array.isArray(points) ? points : [];
            this.applyStatusFilter();
          })
          .catch(() => {
            this.pointsData = [];
            this.renderPointsRows([]);
          });
  },
  getDevices : function(){
    let html = `<option>${this.t('deviceChipId')}</option>`;
    this.selectDev.html("");
  apiFetch(app.routes.app + "?_ld")
      .then(resp => resp.json())
      .then(devices => {
        if(devices.length > 0){
          for(let device of devices){
            html += `
              <option value="${device.id}">${device.id} - ${device.id_chip}</option>
            `;
          }
        }
        this.selectDev.html(html);
    });
  }
};

// Bind admin-only UI events that previously used inline handlers
document.addEventListener('DOMContentLoaded', () => {
  // When clicking the "Agregar punto" button (toggles #form-np), preload devices and users
  const addBtn = document.querySelector('button[data-bs-target="#form-np"]');
  if(addBtn){
    addBtn.addEventListener('click', () => {
      try { appAdmin.getDevices(); } catch(e){}
      try { appAdmin.getUsers(); } catch(e){}
    });
  }

  const filter = document.getElementById('pointsStatusFilter');
  if(filter){
    filter.addEventListener('change', () => {
      try { appAdmin.applyStatusFilter(); } catch(e){}
    });
  }
});
