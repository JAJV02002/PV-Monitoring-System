<?php

if($GLOBALS['tipo'] == "Administrador general"){
?>

<section id="usrs">
      <h1>Usuarios</h1>
            <table class="table border table-hover table-points-grid align-middle">
                <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nombre completo</th>
                <th>Puntos asignados</th>
                <th>Correo electronico</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody id="users">

        </tbody>
        
    </table>
</section>

<?php }