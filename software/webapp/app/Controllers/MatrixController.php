<?php

namespace Controllers;

use Models\dispositivos;
use Models\lecturas;
use Models\matrices;
use Models\puntos;
use Controllers\auth\LoginController as LoginController;

class MatrixController {

    private $userId;
    public $pid;
    public $device_id;
    public $mid;

    private $activeWindowSeconds = 300;

    public function __construct(){
        $ua = new LoginController();
        $ua->sessionValidate();
        $this->userId = $ua->uid;
    }

    /** Guardar nuevo punto
        * @array $datos
        * 
     */
    public function newDevice($datos){
        $device = new dispositivos();
        //'id_chip','mac_address'
        $device->valores = [$datos['chipid'], $datos['mac']];
        $result = $device->create();
        return $result;
        die;
    }

    public function newMatrix($datos){
        $matriz = new matrices();
        //'id_admin_central','name'
        $matriz->valores = [$datos['id_user'],$datos['name']];
        $result = $matriz->create();

        $this->mid = $matriz->lastInsertId();

        return $result;
        
        die;
    }

    public function newCentralPoint($datos, $mid){
        $point = new puntos();
        //'id_device', 'id_matriz', 'id_user', 'name', 'type'
        $name = "Punto central de " . $datos['name'];
        $point->valores = [$datos['id_device'], $mid ,$datos['id_user'], $name, 'Central'];
        $result = $point->create();
        return $result;
        die;
    }

    private function resolveMatrixId($datos){
        $candidate = $datos['id_matriz'] ?? ($datos['id'] ?? null);
        if (!empty($candidate) && ctype_digit((string) $candidate)) {
            return (int) $candidate;
        }

        $matrix = new matrices();
        $result = $matrix->orderBy([['id', 'ASC']])->limit('1')->get();
        $rows = json_decode((string) $result, true);
        if (is_array($rows) && isset($rows[0]['id'])) {
            return (int) $rows[0]['id'];
        }

        return 0;
    }

    public function newPoint($datos){
        $point = new puntos();
        //'id_device', 'id_matriz', 'id_user', 'name', 'type'
        $idMatriz = $this->resolveMatrixId($datos);
        if ($idMatriz <= 0) {
            error_log('No se pudo determinar una matriz válida para crear el punto.');
            return false;
        }

        $point->valores = [
            $datos['id_device'] ?? 0,
            $idMatriz,
            $datos['id_user'] ?? $this->userId,
            $datos['name'] ?? '',
            'Comun'
        ];
        $result = $point->create();
        return $result;
        die;
    }

    public function getDevice($datos){
        $device = new dispositivos();
        $result = $device->where([['id_chip', $datos['chipid']]])->get();

        $rows = json_decode((string) $result, true);
        if (is_array($rows) && count($rows) > 0) {
            $this->device_id = $rows[0]['id'] ?? null;
            return $result;
        }

        $this->device_id = null;
        return json_encode([]);
    }

    public function getDevices(){

        $subquery = "SELECT id_device FROM puntos WHERE id_device = a.id";
        
        $device = new dispositivos();
        $result = $device->select(['a.id','a.id_chip'])
                         ->LEFTjoin('puntos b', 'b.id_device = a.id')
                         ->whereNotExists($subquery)
                         ->orderBy([['a.id', 'ASC']])
                         ->get();
        
        return $result;
    }

    public function getMatrices(){
        $matrices = new matrices();
        $result = $matrices->select(['a.id','a.name', 'u.name AS admin_central',
                                    'COUNT(DISTINCT i.id_user) AS tot_users',
                                    'COUNT(DISTINCT p.id_device) AS tot_devices'])
                            ->LEFTjoin('puntos p', 'a.id = p.id_matriz')
                            ->LEFTjoin('invitaciones i', 'p.id = i.id_punto')
                            ->join('users u', 'a.id_admin_central = u.id')
                            ->groupBy(['a.id', 'a.name'])
                            ->get();
        return $result;
    }

    private function buildLatestReadingsMap(array $pointIds){
        if (count($pointIds) === 0) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $pointIds), function($id){
            return $id > 0;
        })));

        if (count($ids) === 0) {
            return [];
        }

        $lect = new lecturas();
        if (!($lect->table instanceof \mysqli)) {
            return [];
        }

        $sql = 'SELECT id_punto, fecha_captura FROM lecturas WHERE id_punto IN (' . implode(',', $ids) . ') ORDER BY fecha_captura DESC';
        $query = $lect->table->query($sql);
        if (!$query) {
            return [];
        }

        $latestByPoint = [];
        while($row = $query->fetch_assoc()){
            $pid = (int)($row['id_punto'] ?? 0);
            if ($pid <= 0 || isset($latestByPoint[$pid])) {
                continue;
            }
            $latestByPoint[$pid] = $row['fecha_captura'] ?? null;
        }

        return $latestByPoint;
    }

    private function enrichPointsWithRealtimeStatus(array $rows){
        if (count($rows) === 0) {
            return [];
        }

        $pointIds = array_map(function($row){
            return (int)($row['id'] ?? 0);
        }, $rows);

        $latestMap = $this->buildLatestReadingsMap($pointIds);
        $nowTs = time();
        $window = max(60, (int)$this->activeWindowSeconds);

        foreach($rows as &$row){
            $pid = (int)($row['id'] ?? 0);
            $lastReading = $latestMap[$pid] ?? null;
            $isActive = false;

            if ($lastReading) {
                $lastTs = strtotime((string)$lastReading);
                if ($lastTs !== false) {
                    $isActive = (($nowTs - $lastTs) <= $window);
                }
            }

            $row['last_reading_at'] = $lastReading;
            $row['is_active'] = $isActive ? 1 : 0;
            $row['status'] = $isActive ? 'Activo' : 'Inactivo';
        }
        unset($row);

        return $rows;
    }
    
    public function getPoints(){
        $puntos = new puntos();
            $result = $puntos->select(['b.id_chip', 'a.*'])
                             ->LEFTjoin('dispositivos b', 'a.id_device=b.id')
                             ->get();
            $rows = json_decode((string)$result, true);
            if (!is_array($rows)) {
                return json_encode([]);
            }

            $rows = $this->enrichPointsWithRealtimeStatus($rows);
            return json_encode($rows);
    }

    public function getRegisteredPoints($pid){
        $puntos = new puntos();
        $result = $puntos->where([['id_device', $pid]])
                         ->get();

        $rows = json_decode((string) $result, true);
        if (is_array($rows) && count($rows) > 0) {
            $this->pid = $rows[0]['id'] ?? null;
            return json_encode($rows);
        }

        $this->pid = null;
        return json_encode([]);
    }

    public function getUserPoints() {
        $points = new puntos(); // Modelo para manejar la tabla "puntos"    a=puntos b=usuarios c=dispositivos
        $result = $points->select(['a.id', 'a.name', 'a.id_device', 'a.id_user', 'c.id_chip', ])
                         ->join('users b', 'a.id_user = b.id') // Relaciona puntos con usuarios 
                         ->join('dispositivos c', 'a.id_device = c.id')
                         ->where([['a.id_user', $this->userId]])
                         ->orderBy([['a.id', 'ASC']])
                         ->get();
            $rows = json_decode((string)$result, true);
            if (!is_array($rows)) {
                return json_encode([]);
            }

            $rows = $this->enrichPointsWithRealtimeStatus($rows);
            return json_encode($rows);
    }

    public function deletePoint($pid){
        $point = new puntos();
        $r = $point->where([['id',$pid]])->delete();
        return $r;
    }

    public function updatePoint($pid){
        $punto = new puntos();
            $result = $punto->where([['id', $pid]])->update();
            return $result;
    }

}