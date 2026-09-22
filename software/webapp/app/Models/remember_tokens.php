<?php

namespace Models;

use Models\DB;

class remember_tokens extends DB {
    public $table;

    function __construct(){
        parent::__construct();
        $this->table = $this->db_connect();
    }

    // Expected table columns: id, user_id, selector, validator_hash, expires_at, created_at (optional)
    protected $campos = ['user_id','selector','validator_hash','expires_at'];

    public $valores = [];
}
