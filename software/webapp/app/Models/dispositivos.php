<?php

namespace Models;

use Models\DB;

class dispositivos extends DB {
    public $table;

    function __construct(){
        parent::__construct();
        $this->table = $this->db_connect();
    }

    protected $campos = ['id_chip', 'mac_address'];

    public $valores = [];

}