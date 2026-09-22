<?php
namespace views;
require_once __DIR__ . "/../layouts/main.php";

head(new \Controllers\auth\LoginController(), "Sign In | Monitor FV");
?>
<div class="container form-signin w-100 position-absolute top-50 start-50 translate-middle">
    <form id="login-form">
        <!-- <img class="mb-4" src="../assets/brand/bootstrap-logo.svg" alt="" width="72" height="57"> -->
        <h1 class="h3 mb-3 fw-normal">Sign in</h1>
  
        <div class="form-floating">
            <input type="email" class="form-control" id="email" placeholder="name@example.com">
            <label for="floatingInput">Email address</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="passwd" placeholder="Password">
            <label for="floatingPassword">Password</label>
        </div>
  
        <div class="form-check text-start my-3">
            <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
            <label class="form-check-label" for="flexCheckDefault">Remember me</label>
        </div>
        <small class="form-text text-danger d-none" id="error">
            Your login details are incorrect
        </small>
        <input type="hidden" name="_csrf" id="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
        <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
    </form>
</div>

<?php scripts(); ?>
<script src="/resources/js/login.js"></script>