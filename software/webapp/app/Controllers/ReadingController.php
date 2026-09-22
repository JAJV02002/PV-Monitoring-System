<?php


namespace Controllers;

use Models\lecturas;
use Controllers\auth\LoginController as LoginController;

class ReadingController {

    public $userId;

    public function __construct(){
        $ua = new LoginController();
        $ua->sessionValidate();
        $this->userId = $ua->uid;
    }

    public function captureReading($datos, $pid){
        $readings = new lecturas();
        // 'id_punto','consumo_electrico','corriente_RMS','voltaje_RMS','potencia_aparente'
        $readings->valores = [
                    $pid,
                    $datos['energia'], 
                    $datos['corriente'],
                    $datos['voltaje'],
                    $datos['potencia']
                  ];
        $result = $readings->create();
        return $result;
        die;
    }

    public function getPointReadings($userId, $pointId) {
        $readings = new lecturas(); // Modelo para manejar la tabla "lectura" a=lectura b=punto c=usuario
        $result = $readings->select([
                            'a.id', 
                            'a.consumo_electrico', 
                            'a.corriente_RMS', 
                            'a.voltaje_RMS', 
                            'a.potencia_aparente', 
                            'a.fecha_captura', 
                            'b.nombre as punto',
                            'c.nombre as usuario'
                        ])
                        ->join('puntos b', 'a.id_punto = b.id') // Relaciona lecturas con puntos
                        ->join('usuarios c', 'b.id_usuario = c.id') // Relaciona puntos con usuarios
                        ->where([['b.id_usuario', $userId], ['a.id_punto', $pointId]]) // Filtra por usuario y punto
                        ->orderBy([['a.fecha_captura', 'DESC']]) // Ordena por la fecha de captura
                        ->get();
        return $result;
    }

    public function getReadingsRange($rango, $fechaInicio = "", $fechaFin = "") {
        $readings = new lecturas(); // Modelo para manejar la tabla "lectura"
        
        // Construir el filtro de tiempo según el tipo de rango
        $timeCondition = "";
        switch ($rango) {
            case "ayer": // Día anterior
                $condicionTemp = "DATE(lectura.fecha_captura) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case "semana": // Semana actual
                $condicionTemp = "WEEK(lectura.fecha_captura, 1) = WEEK(CURDATE(), 1) 
                                  AND YEAR(lectura.fecha_captura) = YEAR(CURDATE())";
                break;
            case "mes": // Mes actual
                $condicionTemp = "MONTH(lectura.fecha_captura) = MONTH(CURDATE()) 
                                  AND YEAR(lectura.fecha_captura) = YEAR(CURDATE())";
                break;
            case "fecha": // Rango personalizado
                if (!empty($startDate) && !empty($endDate)) {
                    $condicionTemp = "DATE(lectura.fecha_captura) BETWEEN '$fechaInicio' AND '$fechaFin'";
                } else {
                            throw new \Exception("Debes proporcionar una fecha de inicio y una fecha de fin para el rango personalizado.");
                }
                break;
            default:
                    throw new \Exception("El tipo de rango de tiempo no es válido.");
        }
    
        // Realizar la consulta
        $result = $readings->select(['id', 'consumo_electrico', 'corriente_RMS', 'voltaje_RMS', 'potencia_aparente', 'fecha_captura'])
                        ->whereOR([[$condicionTemp]]) // Aplica la condición de tiempo
                        ->orderBy([['lectura.fecha_captura', 'DESC']]) // Ordena por fecha
                        ->get();
    
        return $result;
    }
    
    // Generic JSON API for dashboards
    public function apiGetReadings($params){
        header('Content-Type: application/json');
        $pointId = isset($params['pointId']) ? $params['pointId'] : null;
        $metrics = isset($params['metrics']) ? explode(',', $params['metrics']) : ['consumo_electrico','corriente_RMS','voltaje_RMS','potencia_aparente'];
        $range = isset($params['range']) ? $params['range'] : 'latest';
        $limit = isset($params['limit']) ? intval($params['limit']) : 50;

        // validate metric keys
        $valid = ['consumo_electrico','corriente_RMS','voltaje_RMS','potencia_aparente'];
        $selMetrics = array_values(array_intersect($metrics, $valid));
        if(empty($selMetrics)) $selMetrics = $valid;

        $columns = array_merge(['id','fecha_captura'], $selMetrics);
        $lect = new lecturas();
        $lect->select($columns)
             ->orderBy([['fecha_captura','DESC']])
             ->limit((string)$limit);
        if($pointId){
            $lect->where([['id_punto', $pointId]]);
        }
        $raw = json_decode($lect->get(), true);
        // reverse to chronological order
        $raw = array_reverse($raw);

        $timestamps = [];
        $data = [];
        foreach($selMetrics as $m){ $data[$m] = []; }
        foreach($raw as $row){
            $timestamps[] = $row['fecha_captura'];
            foreach($selMetrics as $m){
                $data[$m][] = isset($row[$m]) ? floatval($row[$m]) : null;
            }
        }
        echo json_encode([ 'timestamps' => $timestamps, 'data' => $data ]);
        exit;
    }

    // Compute time window
    private function computeWindow($range, $start = null, $end = null){
        $now = new \DateTime('now');
        $tz = $now->getTimezone();
        $from = null; $to = null;
        switch(strtolower($range ?? 'hoy')){
            case 'ayer':
                $from = (new \DateTime('yesterday 00:00:00', $tz));
                $to = (new \DateTime('yesterday 23:59:59', $tz));
                break;
            case 'hoy':
                $from = (new \DateTime('today 00:00:00', $tz));
                $to = (new \DateTime('today 23:59:59', $tz));
                break;
            case 'esta semana':
            case 'semana':
                // ISO week: Monday start
                $from = (clone $now)->setTime(0,0,0);
                $w = (int)$from->format('N'); // 1..7
                $from->modify('-'.($w-1).' days');
                $to = (clone $from)->modify('+6 days')->setTime(23,59,59);
                break;
            case 'este mes':
            case 'mes':
                $from = new \DateTime(date('Y-m-01 00:00:00'));
                $to = new \DateTime(date('Y-m-t 23:59:59'));
                break;
            case 'rango de fechas':
            case 'fecha':
                if ($start && $end){
                    $from = new \DateTime($start.' 00:00:00');
                    $to = new \DateTime($end.' 23:59:59');
                }
                break;
            default:
                // fallback: today
                $from = (new \DateTime('today 00:00:00', $tz));
                $to = (new \DateTime('today 23:59:59', $tz));
        }
        return [$from ? $from->format('Y-m-d H:i:s') : null, $to ? $to->format('Y-m-d H:i:s') : null];
    }

    // Stats: averages per metric for a range and optional pointId
    public function apiGetStats($params){
        header('Content-Type: application/json');
        $pointId = $params['pointId'] ?? null;
        $range = $params['range'] ?? 'hoy';
        $start = $params['start'] ?? null;
        $end = $params['end'] ?? null;
        [$from, $to] = $this->computeWindow($range, $start, $end);

        $lect = new lecturas();
        $rows = json_decode(
            $lect->select(['id_punto','consumo_electrico','corriente_RMS','voltaje_RMS','potencia_aparente','fecha_captura'])
                 ->orderBy([[ 'fecha_captura','DESC' ]])
                 ->get(),
            true
        );
        $filtered = array_filter($rows, function($r) use ($pointId, $from, $to){
            if ($pointId && (string)$r['id_punto'] !== (string)$pointId) return false;
            if ($from && $to){
                return ($r['fecha_captura'] >= $from && $r['fecha_captura'] <= $to);
            }
            return true;
        });
        $n = max(count($filtered), 1);
        $sum = [ 'consumo_electrico'=>0, 'corriente_RMS'=>0, 'voltaje_RMS'=>0, 'potencia_aparente'=>0 ];
        foreach($filtered as $r){
            $sum['consumo_electrico'] += (float)($r['consumo_electrico'] ?? 0);
            $sum['corriente_RMS'] += (float)($r['corriente_RMS'] ?? 0);
            $sum['voltaje_RMS'] += (float)($r['voltaje_RMS'] ?? 0);
            $sum['potencia_aparente'] += (float)($r['potencia_aparente'] ?? 0);
        }
        $avg = [];
        foreach($sum as $k=>$v){ $avg[$k] = count($filtered) ? ($v / count($filtered)) : 0; }
        echo json_encode([ 'avg' => $avg, 'count' => count($filtered), 'range' => [ 'from'=>$from, 'to'=>$to ] ]);
        exit;
    }

    // History list filtered by range and point
    public function apiGetHistoryList($params){
        header('Content-Type: application/json');
        $pointId = $params['pointId'] ?? null;
        $range = $params['range'] ?? null;
        $start = $params['start'] ?? null;
        $end = $params['end'] ?? null;
        [$from, $to] = $this->computeWindow($range, $start, $end);

        $lect = new lecturas();
        $rows = json_decode(
            $lect->select(['id_punto','consumo_electrico','corriente_RMS','voltaje_RMS','potencia_aparente','fecha_captura'])
                 ->orderBy([[ 'fecha_captura','DESC' ]])
                 ->get(),
            true
        );
        $filtered = array_filter($rows, function($r) use ($pointId, $from, $to){
            if ($pointId && (string)$r['id_punto'] !== (string)$pointId) return false;
            if ($from && $to){
                return ($r['fecha_captura'] >= $from && $r['fecha_captura'] <= $to);
            }
            return true;
        });
        // Could join to puntos/users to include names; for now, just return raw with id_punto
        echo json_encode(array_values($filtered));
        exit;
    }
    
    
}