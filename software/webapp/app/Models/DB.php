<?php

namespace Models;

use mysqli;


class DB {
    public $db_host;
    public $db_name;
    private $db_user;
    private $db_passwd;

    public $conex;
    public $lastError = null;

    private function readEnv($name, $default = null){
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }
        return $default;
    }

    // Expected to be set by child models (e.g., Lecturas::__construct)
    protected $table; // mysqli connection handle
    protected $campos = [];
    public $valores = [];

    //Variables de control para consultas

    public $s = " * ";
    public $c = "";
    public $j = "";
    public $w = " 1 ";
    public $o = "";
    public $l = "";
    public $g = "";
    
    //...

    public $r; //RESULTADO DE LA CONSULTA

    public function __construct($dbh = null, $dbn = null){
        $this->db_user = $this->readEnv('DB_USER', '');
        $this->db_passwd = $this->readEnv('DB_PASSWORD', '');
        $this->db_host = $dbh ?: $this->readEnv('DB_HOST', 'localhost');
        $this->db_name = $dbn ?: $this->readEnv('DB_NAME', '');
    }

    public function db_connect(){
        $this->conex = @new mysqli($this->db_host, $this->db_user, $this->db_passwd, $this->db_name);
        if (!($this->conex instanceof mysqli)) {
            $this->lastError = 'No se pudo crear la conexión a la base de datos';
            error_log($this->lastError);
            return null;
        }

        $this->conex->set_charset("utf8");
        if($this->conex->connect_error){
            $this->lastError = $this->conex->connect_error;
            error_log('Falló la conexión a la base de datos: ' . $this->lastError);
            $this->conex = null;
            return null;
        }

        return $this->conex;
    }

    public function select($cc = []){
        if(count($cc) > 0){
            $this->s = implode(",",$cc);
        }
        return $this;
    }

    public function count($c = "*"){
        $this->c = ",count(" . $c . ") as tt ";
        return $this;
    }

    public function join($join="",$on=""){
        if($join != "" && $on !=""){
            $this->j .= ' join ' . $join . ' on ' . $on; 
        }
        return $this;
    }

    public function LEFTjoin($join="",$on=""){
        if($join != "" && $on !=""){
            $this->j .= ' left join ' . $join . ' on ' . $on; 
        }
        return $this;
    }

    public function where($ww = []){        
        $this->w = "";
        if(count($ww) > 0){
            foreach($ww as $wheres){
                $this->w .= $wheres[0] . " like '" . $wheres[1] . "' " . ' and ';
            }           
        }
        $this->w .= ' 1 ';
        
        return $this;
    }

    public function whereOR($ww = []){
        $this->w = "";
        if(count($ww) > 0){
            foreach($ww as $wheres){
                $this->w .= $wheres[0] . " like '" . $wheres[1] . "' " . ' OR ';
            }
        }
        $this->w .= ' 1 ';
        return $this;
    }

    public function groupBy($group = []) {
        $this->g = ""; // Inicializar
        if (count($group) > 0) {
            $this->g = ' GROUP BY ' . implode(', ', $group); // Generar la cláusula GROUP BY
        }
        return $this;
    }
    

    public function orderBy($ob=[]){
        $this->o = "";
        if(count($ob) > 0){
            foreach($ob as $orderBy){
                $this->o .= $orderBy[0] . ' ' . $orderBy[1] . ',';
            }
            $this->o = ' order by ' . trim($this->o,',');
        }
        return $this;
    }

    // -------------------------------------------

    public function whereExists($subquery) {
        $this->w = "EXISTS ($subquery)";
        return $this;
    }
    
    public function whereNotExists($subquery) {
        $this->w = "NOT EXISTS ($subquery)";
        return $this;
    }  

    // --------------------------------------------

    public function limit($l = ""){
        $this->l = "";
        if($l != ""){
            $this->l = ' limit ' . $l;
        }
        return $this;
    }

    public function get(){
        if (!$this->table instanceof \mysqli) {
            return json_encode([]);
        }

        $sql = "select " . $this->s . $this->c . 
               " from " . str_replace("Models\\","",get_class($this)) .
               ($this->j != "" ? " a " . $this->j : "" ) .
               " where " . $this->w . 
               $this->g .
               $this->o .
               $this->l;
        //echo $sql;
        $this->r = $this->table->query($sql);
        if (!$this->r) {
            error_log('DB query failed: ' . $this->table->error);
            return json_encode([]);
        }
        $result = [];
        while($f = $this->r->fetch_assoc()){
            $result[] = $f;
        }
        return json_encode($result);
    }

    public function create(){
        if (!$this->table instanceof \mysqli) {
            return false;
        }

        if (count($this->campos) !== count($this->valores)) {
            error_log('DB create failed: expected ' . count($this->campos) . ' values for ' . count($this->campos) . ' columns, received ' . count($this->valores));
            return false;
        }

        $sql = 'insert into ' . str_replace("Models\\","",get_class($this)) . 
                ' (' . implode(',',$this->campos) . ') values (' . 
                trim(str_replace("&","?,",str_pad("",count($this->campos),"&")),",") . ');';

        try {
            $stmt = $this->table->prepare($sql);
            if (!$stmt) {
                $this->lastError = $this->table->error;
                error_log('DB create prepare failed: ' . $this->lastError);
                return false;
            }

            $stmt->bind_param(str_pad("",count($this->campos),"s"),...$this->valores);
            if (!$stmt->execute()) {
                $this->lastError = $stmt->error;
                error_log('DB create execute failed: ' . $this->lastError);
                return false;
            }

            return true;
        } catch (\mysqli_sql_exception $e) {
            $this->lastError = $e->getMessage();
            error_log('DB create exception: ' . $this->lastError);
            return false;
        }
    }

    public function lastInsertId() {
        // Ejecutar la consulta de LAST_INSERT_ID
        $result = $this->conex->query("SELECT LAST_INSERT_ID() AS last_id");
    
        // Verificar y devolver el resultado
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['last_id'];
        } else {
            return null; // Retorna null si no hay resultados
        }
    }
    

    public function delete(){
        if (!$this->table instanceof \mysqli) {
            return false;
        }

        $sql = 'delete  from ' . str_replace("Models\\","",get_class($this)) . 
                " where " . $this->w;
        
        $result = $this->table->query($sql);

        return $result;

    }

    public function update(){
        if (!$this->table instanceof \mysqli) {
            return false;
        }

        $sets = [];
        foreach($this->valores as $key=>$value){
            $sets[] = $key . "='" . $value ."'";
        }
        $sql = 'update ' . str_replace("Models\\","",get_class($this)) . 
                ' set ' . implode(",",$sets) . ' where ' .$this->w;
        //echo $sql . " - ";
        $result = $this->table->query($sql);

        return $result;
                
    }

} 