<?php

namespace Models;

use Models\DB;

class puntos extends DB {
    public $table;

    function __construct(){
        parent::__construct();
        $this->table = $this->db_connect();
    }

    protected $campos = ['id_device', 'id_matriz', 'id_user', 'name', 'type'];

    public $valores = [];

}