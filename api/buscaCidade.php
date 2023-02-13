<?php
include "../config.php";
header('Content-Type: application/json; ');


$db = new DB();

if (isset($_GET['estado'])) {
    $q = "SELECT cidade.* FROM sistema_oriente.estado, sistema_oriente.cidade WHERE estado.idEstado=cidade.idEstado AND estado.ufEstado='{$_GET['estado']}' ;";
    $res = $db->query($q);
    if (!$res) die($db->error());
    $r = array();
    while ($row = $res->fetch_assoc()) {
        array_push($r, $row);
    }
    echo json_encode($r);
}
