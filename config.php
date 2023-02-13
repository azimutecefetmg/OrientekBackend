<?php

class DB {
    public $conn;

    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'sistema_oriente');
        if ($this->conn) {
            $this->conn->set_charset('utf8');
        } else {
            echo 'Database Erro ';
        }
    }

    public function executeUpdate($query, $idClube) {
        $res = $this->conn->query($query);
        $time = date('y-m-d h:m:s', time());
        if ($res) $this->conn->query("UPDATE sistema_oriente.clube SET lastEdited={$time} WHERE idClube={$idClube}");
        return $res;
    }

    public function query($q) {
        return $this->conn->query($q);
    }

    public function error() {
        return $this->conn->error;
    }
}

