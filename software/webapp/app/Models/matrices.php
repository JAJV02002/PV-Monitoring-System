<?php

namespace Models;

use Models\DB;

class matrices extends DB {
    public $table;

    function __construct(){
        parent::__construct();
        $this->table = $this->db_connect();
    }

    protected $campos = ['id_admin_central','name'];

    public $valores = [];

}