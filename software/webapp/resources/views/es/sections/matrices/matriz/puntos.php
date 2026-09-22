<section id="pts">
    <div class="row align-items-center g-2">
        <h1 class="col-md border-bottom pb-1 mb-0">Puntos registrados</h1>
        <?php 
        if($GLOBALS['tipo'] == "Administrador general"){ 
        ?>
        <button href="#" role="button" class="btn btn-primary col-2 my-auto me-3" data-bs-toggle="collapse" data-bs-target="#form-np" aria-expanded="false" aria-controls="form-np">
            Agregar punto
        </button>
    <form class="row mb-3 collapse" action="/app/app.php" method="POST" id="form-np">
            <div class="input-group w-75">
                <label class="form-control bg-secondary text-white">Nuevo punto</label>
                <input type="hidden" name="_ap" value="true">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
                <input type="hidden" name="id_matriz" value="<?= htmlspecialchars($_GET['id_matriz'] ?? ($_GET['id'] ?? 0)) ?>">
                <input class="form-control" name="name" type="text" placeholder="Nombre de punto" required>
                <select class="form-select" name="id_device" id="device_id" required>
                    
                    
                </select>
                <select class="form-select" name="id_user" id="user_id" required>

                </select>
                <button class="btn btn-outline-primary" type="submit">Guardar</button>
            </div>
        </form>
        <?php }?>
    </div>
    <div class="row g-5">
            <div class="col">
                <table class="table border table-hover table-points-grid align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Dispositivo</th>
                            <th>Usuario</th>
                            <th>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <span>Estado</span>
                                    <div class="status-filter-icon-wrap" title="Filtrar por estado">
                                        <svg class="bi status-filter-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#funnel"/></svg>
                                        <select id="pointsStatusFilter" class="status-filter-icon-select" aria-label="Filtrar por estado">
                                            <option value="all">Todos</option>
                                            <option value="active">Activos</option>
                                            <option value="inactive">Inactivos</option>
                                        </select>
                                    </div>
                                </div>
                            </th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablePts">
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron puntos registrados</td>
                        </tr>
                    </tbody>
                </table>
            </div>
    </div>
      
      
</section>