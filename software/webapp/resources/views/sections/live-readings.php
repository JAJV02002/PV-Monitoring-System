<section id="tiempo-real" class="mt-3">
  <h1>Tiempo Real</h1>

  <form class="row g-3" action="javascript:void(0)">
    <div class="input-group w-50">
      <select class="form-select" id="selPoint">
        <option value="">Selecciona un punto</option>
      </select>
      <button class="btn btn-outline-secondary" id="btnVer" type="button">Ver</button>
    </div>
  </form>

  <?php
    $config = [
      'pointId' => null,
      'pollMs' => 5000,
      'limit' => 60,
      'metrics' => [
        ['key'=>'consumo_electrico', 'label'=>'Consumo eléctrico (kWh)', 'unit'=>'kWh'],
        ['key'=>'corriente_RMS', 'label'=>'Corriente RMS (A)', 'unit'=>'A'],
        ['key'=>'voltaje_RMS', 'label'=>'Voltaje RMS (V)', 'unit'=>'V'],
        ['key'=>'potencia_aparente', 'label'=>'Potencia aparente (W)', 'unit'=>'W'],
      ]
    ];
    include __DIR__ . '/../components/readings/dashboards.php';
  ?>
  
</section>