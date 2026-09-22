<section id="historico" class="mt-3">
    <h1>Lecturas histórico</h1>
    <form class="row g-3" action="javascript:void(0)">
        <div class="input-group w-75">
            <select class="form-select" id="selPointH">
                <option value="">Todos los puntos</option>
            </select>
            <select class="form-select" id="selRange">
                <option value="hoy">Hoy</option>
                <option value="ayer">Ayer</option>
                <option value="esta semana">Esta semana</option>
                <option value="este mes">Este mes</option>
                <option value="rango de fechas">Rango de fechas</option>
            </select>
            <input class="form-control d-none" type="date" id="fechaInicio">
            <input class="form-control d-none" type="date" id="fechaFin">
            <button class="btn btn-outline-secondary" id="btnBuscar" type="button">Buscar</button>
        </div>
    </form>

    <div class="row g-3 my-3" id="kpis">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Consumo eléctrico (kWh)</h6><div class="display-6" id="kpi-consumo">-</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Corriente RMS (A)</h6><div class="display-6" id="kpi-corriente">-</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Voltaje RMS (V)</h6><div class="display-6" id="kpi-voltaje">-</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Potencia aparente (VA)</h6><div class="display-6" id="kpi-potencia">-</div></div></div></div>
    </div>

    <div id="tablaHistorico"></div>
</section>