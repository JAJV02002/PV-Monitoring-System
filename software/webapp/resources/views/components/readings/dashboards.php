<?php
// Reusable dashboards component
// Usage: include this file with $config array:
// $config = [
//   'pointId' => '123', 'pollMs' => 5000,
//   'metrics' => [
//      ['key'=>'voltaje_RMS','label'=>'Voltaje RMS (V)','unit'=>'V'],
//      ['key'=>'corriente_RMS','label'=>'Corriente RMS (A)','unit'=>'A'],
//      ...
//   ]
// ];
if(!isset($config)) { $config = []; }
$metrics = $config['metrics'] ?? [
	['key'=>'consumo_electrico', 'label'=>'Consumo eléctrico (kWh)', 'unit'=>'kWh'],
	['key'=>'corriente_RMS', 'label'=>'Corriente RMS (A)', 'unit'=>'A'],
	['key'=>'voltaje_RMS', 'label'=>'Voltaje RMS (V)', 'unit'=>'V'],
	['key'=>'potencia_aparente', 'label'=>'Potencia aparente (W)', 'unit'=>'W'],
];
?>

<?php
	// Build config payload to pass via data attribute (avoids inline scripts)
	$dashCfg = [
		'api' => '/app/api.php',
		'pointId' => $config['pointId'] ?? null,
		'range' => $config['range'] ?? 'latest',
		'pollMs' => $config['pollMs'] ?? 5000,
		'limit' => $config['limit'] ?? 50,
		'metrics' => $metrics
	];
?>

<section class="container my-3" id="dashboards" data-dash-config='<?= htmlspecialchars(json_encode($dashCfg), ENT_QUOTES, "UTF-8") ?>'>
	<div class="row g-3">
		<?php foreach($metrics as $m): ?>
			<div class="col-md-6">
				<div class="card">
					<div class="card-body">
						<h6 class="card-title"><?= htmlspecialchars($m['label']) ?></h6>
						<div class="d-flex align-items-baseline gap-2 mb-2">
							<div class="display-6 lh-1" data-current-value>-</div>
							<small class="text-muted"><?= htmlspecialchars($m['unit']) ?></small>
						</div>
						<div class="w-100" data-metric="<?= htmlspecialchars($m['label']) ?>" data-key="<?= htmlspecialchars($m['key']) ?>" data-unit="<?= htmlspecialchars($m['unit']) ?>">
							<canvas height="140"></canvas>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
