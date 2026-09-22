<?php

namespace Models;

use Models\DB;

class lecturas extends DB {
    public $table;

    function __construct(){
        parent::__construct();
        $this->table = $this->db_connect();
    }

    protected $campos = ['id_punto','consumo_electrico','corriente_RMS','voltaje_RMS','potencia_aparente'];

    public $valores = [];

}