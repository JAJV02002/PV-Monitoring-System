<?php
namespace views;
require_once "./resources/views/layouts/main.php";

head();
?>
<div class="container form-signin w-100 position-absolute top-50 start-50 translate-middle">
    <form id="login-form">
        <!-- <img class="mb-4" src="../assets/brand/bootstrap-logo.svg" alt="" width="72" height="57"> -->
        <h1 class="h3 mb-3 fw-normal">Inicio de sesión</h1>
  
        <div class="form-floating">
            <input type="email" class="form-control" id="email" placeholder="name@ejemplo.com">
            <label for="floatingInput">Dirección Email</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="passwd" placeholder="Contraseña">
            <label for="floatingPassword">Contraseña</label>
        </div>
  
        <div class="form-check text-start my-3">
            <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
            <label class="form-check-label" for="flexCheckDefault">Recordar</label>
        </div>
        <small class="form-text text-danger d-none" id="error">
            Sus datos de inicio de sesión son incorrectos
        </small>
        <input type="hidden" name="_csrf" id="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
        <button class="btn btn-primary w-100 py-2" type="submit">Iniciar sesión</button>
    </form>
</div>

<?php scripts(); ?>
<script src="/resources/js/login.js"></script>