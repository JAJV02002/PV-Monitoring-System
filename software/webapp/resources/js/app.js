function readMeta(name){
  const el = document.querySelector(`meta[name="${name}"]`);
  return el ? el.getAttribute('content') : null;
}

const app = {
    //Directorio de rutas
    routes : {
      app : "/app/api.php",
      newPto : "/app/api.php?_np",
      insertLectura : "?_il",
      loadLectura : "?_ll",
      loadHistorico : "?_lh"
    },
    user : {
        sv : false,
        id : "",
        tipo : "",
        uid : ""
    },
    view : function(route){
        location.replace(app.routes[route]);
    },


    // Inicializa el navbar y añade la clase 'active' al elemento seleccionado
    initNav: function () {
        // Seleccionar todos los enlaces del menú de navegación
        const navLinks = document.querySelectorAll('#nav-bar .nav-link');
    
        // Obtener la URL actual para mantener la clase 'active' en la página actual
        const currentPath = window.location.pathname;
    
        // Función para manejar el cambio de la clase 'active'
        function setActiveLink(event) {
          // Eliminar la clase 'active' de todos los enlaces
          navLinks.forEach(link => link.classList.remove('active'));
    
          // Agregar la clase 'active' al enlace clicado
          event.target.classList.add('active');
        }
    
        // Inicializar la clase 'active' según la URL actual
        navLinks.forEach(link => {
          if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
          } else {
            link.classList.remove('active');
          }
        });
    
        // Añadir un evento 'click' a cada enlace para manejar el cambio dinámico
        navLinks.forEach(link => {
          link.addEventListener('click', setActiveLink);
        });
      },
    
      // Otras funciones de la aplicación
      init: function () {
        const currentPath = window.location.pathname;
        const authPaths = ['/login', '/register', '/en/login', '/en/register', '/es/login', '/es/register'];
        if (authPaths.includes(currentPath)) {
          return;
        }

        // Populate user from meta tags (no inline JS needed)
        const sv = readMeta('app-sv');
        const uid = readMeta('app-uid');
        const tipo = readMeta('app-tipo');
        if(sv !== null) this.user.sv = (sv === 'true');
        if(uid !== null) { this.user.id = uid; this.user.uid = uid; }
        if(tipo !== null) this.user.tipo = tipo;
        this.initNav(); // Inicializa la navegación
        // Llama aquí a otras funciones iniciales de la app
        if(this.user.tipo === "Administrador general"){
          appAdmin.getDevices();
          appAdmin.getUsers();
          appAdmin.getPoints();
          appAdmin.getMatrices();
        } else {
          appUser.getPoints();
        }
        
      },

    };
    
    // Inicializar la aplicación al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
      app.init();
    });





