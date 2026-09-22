<?php

    namespace views;
    require_once "./resources/views/layouts/main.php";

    head();

?>
<div class="container position-absolute top-50 start-50 translate-middle">
    <div class="card mt-5 w-50 mx-auto">
        <div class="card-body">
            <form action="" id="register-form">
                <div class="form-group">
                    <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" 
                            id="name"
                            class="form-control"
                            name="name"
                            placeholder="Nombre completo"
                            required>   
                </div>
                <div class="form-group">
                    <label for="email">Correo electronico</label>
                    <input type="email" 
                            id="email"
                            class="form-control"
                            name="email"
                            placeholder="ej.: micorreo@mail.com"
                            required>   
                </div>
                    <label for="username">Usuario</label>
                    <input type="text" 
                            id="username"
                            class="form-control"
                            name="username"
                            placeholder="Nombre de usuario"
                            required>   
                </div>
                <div class="form-group">
                    <label for="passwd">Contraseña</label>
                    <input type="password" 
                            class="form-control" 
                            id="passwd"
                            name="passwd"
                            required>
                </div>
                <div class="form-group">
                    <label for="cpasswd">Confirmar contraseña</label>
                    <input type="password" 
                            class="form-control" 
                            id="cpasswd"
                            name="cpasswd"
                            required>
                </div>
                <div class="d-grid gap-2 my-2">
                    <small class="form-text text-danger d-none" id="error">
                        Sus datos de registro son incorrecctos
                    </small>
                    <small class="form-text text-danger d-none" id="errorc">
                        Las contraseñas no coinciden
                    </small>
                    <input type="hidden" name="_csrf" id="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
                    <button class="btn btn-primary" type="submit">
                        Registrar <i class="bi bi-box-arrow-in-right"></i>
                    </button>
                    <button class="btn btn-link float-end" onclick="app.view('inisession');">Iniciar sesion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php scripts(); ?>

<script type="text/javascript">
    $(function(){
        const rf = $("#register-form");
        rf.on("submit", function(e){
            e.preventDefault();
            e.stopPropagation();
            const data = new URLSearchParams();
            data.set("username",$("#username").val());
            data.set("name",$("#name").val());
            data.set("email",$("#email").val());
            data.set("passwd",$("#passwd").val());
            if($("#passwd").val() === $("#cpasswd").val()){
                data.set("_register","1");
                data.set("_csrf", $("#csrf").val());
                fetch(app.routes.app,{ method : "POST", credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body : data.toString() })
                .then ( resp => resp.json())
                .then ( resp => {
                    if(resp.r !== false){
                        location.href = "/";
                        //app.view("home");
                    }else{
                        $("#error").removeClass("d-none");
                    }
                });
            } else{
                $("#errorc").removeClass("d-none");
                
            }
            
        })
    })
</script>