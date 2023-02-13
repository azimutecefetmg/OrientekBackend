<?php
/**
 * Created by PhpStorm.
 * User: josoe
 * Date: 28/01/19
 * Time: 09:31
 */
include "../config.php";
header('Content-Type: application/json; ');

$db = new DB();
$q = "SELECT * FROM sistema_oriente.estado ;";
$res = $db->query($q);
if (!$res) die($db->error());
$r = array();
while ($row = $res->fetch_assoc()) {
    array_push($r, $row);
}
echo json_encode($r);

