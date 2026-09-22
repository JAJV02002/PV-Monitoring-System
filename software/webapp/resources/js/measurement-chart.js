// Configuración de los gráficos en Chart.js
const currentCtx = document.getElementById('currentChart').getContext('2d');
const voltageCtx = document.getElementById('voltageChart').getContext('2d');
const powerCtx = document.getElementById('powerChart').getContext('2d');
const energyCtx = document.getElementById('energyChart').getContext('2d');

const createChart = (ctx, label, color) => {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // Tiempos de actualización
            datasets: [{
                label: label,
                data: [], // Datos de lecturas
                borderColor: color,
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { display: true },
                y: { display: true }
            }
        }
    });
};

// Crear gráficos específicos para cada tipo de lectura
const currentChart = createChart(currentCtx, 'Corriente RMS (A)', 'rgba(75, 192, 192, 1)');
const voltageChart = createChart(voltageCtx, 'Voltaje RMS (V)', 'rgba(54, 162, 235, 1)');
const powerChart = createChart(powerCtx, 'Potencia Aparente (W)', 'rgba(255, 206, 86, 1)');
const energyChart = createChart(energyCtx, 'Consumo Eléctrico (kWh)', 'rgba(153, 102, 255, 1)');

// Función para actualizar los gráficos con nuevos datos
function updateCharts(data) {
    const timestamp = new Date().toLocaleTimeString();

    // Actualizar cada gráfico con los datos nuevos
    currentChart.data.labels.push(timestamp);
    currentChart.data.datasets[0].data.push(data.current_rms);
    if (currentChart.data.labels.length > 20) currentChart.data.labels.shift();
    currentChart.update();

    voltageChart.data.labels.push(timestamp);
    voltageChart.data.datasets[0].data.push(data.voltage_rms);
    if (voltageChart.data.labels.length > 20) voltageChart.data.labels.shift();
    voltageChart.update();

    powerChart.data.labels.push(timestamp);
    powerChart.data.datasets[0].data.push(data.apparent_power);
    if (powerChart.data.labels.length > 20) powerChart.data.labels.shift();
    powerChart.update();

    energyChart.data.labels.push(timestamp);
    energyChart.data.datasets[0].data.push(data.energy_consumption);
    if (energyChart.data.labels.length > 20) energyChart.data.labels.shift();
    energyChart.update();
}

// Función para obtener datos desde la API (usando HTTP)
function fetchData() {
    fetch('get-data.php')
        .then(response => response.json())
        .then(data => {
            updateCharts(data);
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Llamar a `fetchData` cada minuto para actualizar los gráficos
setInterval(fetchData, 60000); // 60000 ms = 1 min
