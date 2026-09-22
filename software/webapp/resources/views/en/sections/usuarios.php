<?php

if($GLOBALS['tipo'] == "Administrador general"){
?>

<section id="usrs">
      <h1>Users</h1>
            <table class="table border table-hover table-points-grid align-middle">
                <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Full name</th>
                <th>Assigned points</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="users">

        </tbody>
        
    </table>
</section>

<?php }