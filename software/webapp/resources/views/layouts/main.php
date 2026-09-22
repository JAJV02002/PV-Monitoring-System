<?php 
require_once './app/autoloader.php';
use Controllers\auth\LoginController as LoginController;
$sv;
$uid = "";
$name = "";
$tipo = "";

function head($ua = new LoginController(), $title = "Monitor FV") {
    $ua->sessionValidate();
    $GLOBALS['sv'] = $ua->sv ? 'true': 'false';
    $GLOBALS['uid'] = "$ua->uid";
    $GLOBALS['name'] = "$ua->name";
    $GLOBALS['tipo'] = "$ua->type";
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
    <meta name="app-sv" content="<?= htmlspecialchars($GLOBALS['sv'] ?? '') ?>">
    <meta name="app-uid" content="<?= htmlspecialchars($GLOBALS['uid'] ?? '') ?>">
    <meta name="app-name" content="<?= htmlspecialchars($GLOBALS['name'] ?? '') ?>">
    <meta name="app-tipo" content="<?= htmlspecialchars($GLOBALS['tipo'] ?? '') ?>">
    <link href="/resources/css/bootstrap.css" rel="stylesheet">
    <title><?= htmlspecialchars($title) ?></title>

    

    <link rel="stylesheet" href="/resources/css/sidebars.css">
    <link rel="stylesheet" href="/resources/css/style.css">
    <link href="/resources/css/sign-in.css" rel="stylesheet">

    <!-- ICONS -->
    <?php include_once __DIR__ . '/../components/icons.php'; ?>
    <!-- ICONS -->

</head>
<?php }

function body() { ?>
<body>
<main id="app" class="d-flex flex-nowrap">
    <?php include_once __DIR__ . '/../components/theme-switcher.php';?>
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
    <!-- Content -->
    <div class="container-fluid overflow-auto">
    <?php // Global modal component available across pages ?>
    <?php if (file_exists(__DIR__ . '/../components/alert-modal.php')) { include __DIR__ . '/../components/alert-modal.php'; } ?>

<?php }

function scripts($script = "") { ?>
    </div>
</main>
<script src="/resources/js/jquery.js"></script>
<script src="/resources/js/bootstrap.js"></script>
<script src="/resources/js/api.js"></script>
<!-- Chart.js for dashboards -->
<script src="/resources/js/chart.js"></script>
<script src="/resources/js/app.js"></script>
<!-- Generic dashboards renderer -->
<script src="/resources/js/dashboards.js"></script>
<!-- App modal helper -->
<script src="/resources/js/modal.js"></script>
<!-- Page-specific bindings (tiempo_real, historico) for all roles -->
<script src="/resources/js/pages.js"></script>
<!-- Removed inline init to comply with strict CSP; app.js reads meta tags instead -->
<?php 
    if($GLOBALS['tipo'] == "Administrador general"){
?>
        <script src="/resources/js/admin.js"></script>

<?php } else {?>
    <script src="/resources/js/user.js"></script>
<?php } ?>

<script src="/resources/js/color-modes.js"></script>
<?php if ($script != ''): ?>
    <script src="/resources/js/<?php echo $script; ?>"></script>
<?php endif; 
 }

function foot() { ?>
</body>
</html>
<?php }
?>

