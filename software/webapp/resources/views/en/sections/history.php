<section id="historico" class="mt-3">
    <h1>Historical Readings</h1>
    <form class="row g-3" action="javascript:void(0)">
        <div class="input-group w-75">
            <select class="form-select" id="selPointH">
                <option value="">All points</option>
            </select>
            <select class="form-select" id="selRange">
                <option value="hoy">Today</option>
                <option value="ayer">Yesterday</option>
                <option value="esta semana">This week</option>
                <option value="este mes">This month</option>
                <option value="rango de fechas">Date range</option>
            </select>
            <input class="form-control d-none" type="date" id="fechaInicio">
            <input class="form-control d-none" type="date" id="fechaFin">
            <button class="btn btn-outline-secondary" id="btnBuscar" type="button">Search</button>
        </div>
    </form>

    <div class="row g-3 my-3" id="kpis">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Energy consumption (kWh)</h6><div class="display-6" id="kpi-consumo">-</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Current RMS (A)</h6><div class="display-6" id="kpi-corriente">-</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Voltage RMS (V)</h6><div class="display-6" id="kpi-voltaje">-</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Apparent power (VA)</h6><div class="display-6" id="kpi-potencia">-</div></div></div></div>
    </div>

    <div id="tablaHistorico"></div>
</section>