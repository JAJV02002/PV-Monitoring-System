<?php

namespace Models;

use Models\DB;

class users extends DB {
    public $table;

    function __construct(){
        parent::__construct();
        $this->table = $this->db_connect();
    }

    protected $campos = ['username','name','email','password'];

    public $valores = [];

}