(function(){
  $(function(){
    const lf = $("#login-form");
    lf.on("submit", function(e){
      e.preventDefault();
      e.stopPropagation();
      const payload = new URLSearchParams();
      payload.set("email", $("#email").val());
      payload.set("passwd", $("#passwd").val());
      payload.set("tipo", "");
      const remember = document.getElementById('flexCheckDefault').checked ? '1' : '0';
      payload.set("remember", remember);
      payload.set("_login", "1");
      const token = document.querySelector('meta[name="csrf-token"]').content || $("#csrf").val();
      payload.set("_csrf", token);
      fetch(app.routes.app, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: payload.toString() })
        .then(async resp => {
          const text = await resp.text();
          let data = {};
          try {
            data = text ? JSON.parse(text) : {};
          } catch (err) {
            throw new Error('La respuesta del servidor no es JSON válido.');
          }
          if(!resp.ok || data.error){
            throw new Error(data.error || 'No se pudo iniciar sesión.');
          }
          return data;
        })
        .then(resp => {
          if(resp.r !== false){
            const lang = (document.querySelector('meta[name="app-lang"]')?.getAttribute('content') || 'es').toLowerCase();
            location.href = (lang === 'en') ? '/en' : '/';
          }else{
            $("#error").removeClass("d-none");
            $("#error").text("Sus datos de inicio de sesión son incorrectos");
          }
        })
        .catch(err => {
          console.error(err);
          $("#error").removeClass("d-none");
          $("#error").text(err.message || 'No se pudo iniciar sesión. Verifica la conexión al servidor.');
        });
    })
  })
})();
