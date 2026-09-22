<?php
// Generic table for readings; expects $rows as array of associative arrays
if(!isset($rows)) $rows = [];
?>
<div class="table-responsive">
	<table class="table border table-hover table-points-grid align-middle table-sm">
		<thead class="table-light">
			<tr>
				<th>Punto</th>
				<th>Consumo eléctrico (kWh)</th>
				<th>Corriente RMS (A)</th>
				<th>Voltaje RMS (V)</th>
				<th>Potencia aparente (VA)</th>
				<th>Fecha</th>
			</tr>
		</thead>
		<tbody>
			<?php if(empty($rows)): ?>
				<tr><td colspan="6" class="text-center">Sin datos</td></tr>
			<?php else: foreach($rows as $r): ?>
				<tr>
					<td><?= htmlspecialchars($r['punto'] ?? '-') ?></td>
					<td><?= htmlspecialchars($r['consumo_electrico'] ?? '-') ?></td>
					<td><?= htmlspecialchars($r['corriente_RMS'] ?? '-') ?></td>
					<td><?= htmlspecialchars($r['voltaje_RMS'] ?? '-') ?></td>
					<td><?= htmlspecialchars($r['potencia_aparente'] ?? '-') ?></td>
					<td><?= htmlspecialchars($r['fecha_captura'] ?? '-') ?></td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
