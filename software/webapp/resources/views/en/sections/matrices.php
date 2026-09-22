<section class="card mt-3" id="matrices">
    <div class="card-header">
        <?php 
        if($GLOBALS['tipo'] == "Administrador general"){ 
        ?>
        <h1 class="card-title">Matrices</h1>
    <form class="row mb-3" action="/app/app.php" method="POST" id="form-np">
            <div class="input-group w-75">
            <label class="form-control bg-secondary text-white">New matrix</label>
                <input type="hidden" name="_nm" value="true">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
            <input class="form-control" name="name" type="text" placeholder="Point name" required>
                <select class="form-select" name="id_device" id="device_id" required>
                    
                </select>
                <select class="form-select" name="id_user" id="user_id" required>

                </select>
                <input class="form-control d-none" type="week" name="" id="btnWeek">
                <input class="form-control d-none" type="month" name="" id="btnMonth">
                <button class="btn btn-outline-primary" type="submit">Save</button>
            </div>
        </form>
        <?php }?>
    </div>
      
    <div class="card-body g-2" id="grid-matrices">
    

        
    </div>
</section>