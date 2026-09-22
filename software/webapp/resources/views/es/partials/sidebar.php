
<div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 280px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
      <svg class="bi pe-none me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>
      <span class="fs-4">Monitor FV</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto" id="nav-bar">
      <li class="nav-item">
        <a href="/" class="nav-link link-body-emphasis" aria-current="page" data-section="inicio">
          <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#home"/></svg>
          Inicio
        </a>
      </li>

      <li class="nav-item">
      <a href="#" class="nav-link disabled text-secondary" data-section="pts" aria-disabled="true" tabindex="-1" title="Módulo deshabilitado temporalmente">
          <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
          Matrices
        </a>
      </li>
      
      <li class="nav-item">
        <a href="/puntos" class="nav-link link-body-emphasis" data-section="pts" onclick="appUser.getPuntos()">
          <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
          Puntos
        </a>
      </li>

      
      <?php if($GLOBALS['tipo'] == 'Administrador general') { ?>
        <li class="nav-item">
          <a href="/usuarios" class="nav-link link-body-emphasis" data-section="usrs">
            <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#people"/></svg>
            Usuarios
          </a>
        </li>
      <?php } ?>
      

      <li class="nav-item">
        <a href="/tiempo_real" class="nav-link link-body-emphasis" data-section="tiempo-real">
          <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Tiempo real
        </a>
      </li>
      <li class="nav-item">
        <a href="/historico" class="nav-link link-body-emphasis" data-section="historico">
          <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#table"/></svg>
          Historico
        </a>
      </li>
    </ul>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
        <strong>
          <?= $GLOBALS['name'] ?>
        </strong>
      </a>
      <ul class="dropdown-menu text-small shadow">
        <!-- <li><a class="dropdown-item" href="#">New project...</a></li> -->
        <!-- <li><a class="dropdown-item" href="#">Settings</a></li> -->
        <li><a class="dropdown-item" href="/perfil?id=<?=$GLOBALS['uid']?>">Perfil</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/app/app.php?_logout">Cerrar sesión</a></li>
      </ul>
    </div>
  </div>