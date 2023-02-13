<?php
include_once('../config.php');
//if ($_SERVER['REQUEST_METHOD'] === 'GET'){
    $db  = new DB();
    $res = $db->query("SELECT nomeClube,idClube FROM sistema_oriente.clube");
    if(!$res) die($db->error());
    $r = array();
    while($row = $res->fetch_assoc()){
        array_push($r,$row);
    }
    echo json_encode($r);
//}else{
    // Bad request -> requisição mau feita ou incompativel
//    http_response_code(400);
//}
