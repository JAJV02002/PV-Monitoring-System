<section id="tiempo-real" class="mt-3">
  <h1>Real Time</h1>

  <form class="row g-3" action="javascript:void(0)">
    <div class="input-group w-50">
      <select class="form-select" id="selPoint">
        <option value="">Select a point</option>
      </select>
      <button class="btn btn-outline-secondary" id="btnVer" type="button">View</button>
    </div>
  </form>

  <?php
    $config = [
      'pointId' => null,
      'pollMs' => 5000,
      'limit' => 60,
      'metrics' => [
        ['key'=>'consumo_electrico', 'label'=>'Energy consumption (kWh)', 'unit'=>'kWh'],
        ['key'=>'corriente_RMS', 'label'=>'Current RMS (A)', 'unit'=>'A'],
        ['key'=>'voltaje_RMS', 'label'=>'Voltage RMS (V)', 'unit'=>'V'],
        ['key'=>'potencia_aparente', 'label'=>'Apparent power (W)', 'unit'=>'W'],
      ]
    ];
    include __DIR__ . '/../components/readings/dashboards.php';
  ?>
  
</section>