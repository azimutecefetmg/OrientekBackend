<?php

include_once('config.php');
$DB = new DB();
header('Content-Type: application/json');

function testaLogin($email, $senha) {
    $DB = new DB();
    $query = "SELECT senhaClube,idClube FROM sistema_oriente.clube WHERE loginClube='{$email}'";
    $r = $DB->query($query);
    if ($r) {
        if ($r->num_rows) {
            $user = $r->fetch_assoc();
            if (sha1($senha) === $user['senhaClube']) {
                return $user['idClube'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        echo $DB->error();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO Remover linha abaixo
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo '{error:"' . json_last_error_msg() . '"}';
    }
    if (isset($data['email']) && isset($data['senha'])) {
        $login = testaLogin($data['email'], $data['senha']);
        if ($login) {
            $cstrong = True;
            // TODO Configure token expiration
            $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
            $time = time() + 60 * 60 * 24 * 15;
            $r = $DB->executeUpdate("UPDATE sistema_oriente.clube SET tokenExpiration={$time}, authToken='${token}' WHERE idClube={$login}",$login);
            if ($r) {
                echo '{"token":"' . $token . '", "id":"' . $login . '", "expiration":"'.$time.'", "time" :"'.time().'"}';
            } else {
                echo '{"error":"' . $DB->error() . '"}';
            }
        }
    } else if (isset($data['token']) && isset($data['id'])) {
        // TODO Validar expiração do token
        $r = $DB->query("SELECT clube.* FROM sistema_oriente.clube WHERE idClube={$data['id']} AND authToken='{$data['token']}'");
        if (!$r) die($DB->error());
        if ($r->num_rows > 0) {
            $r = $r->fetch_assoc();
            if($r['tokenExpiration'] < time()){
                http_response_code(401);
            }
            $r['senhaClube'] = '';
            echo json_encode($r);
        }
    } else {
        http_response_code(401);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: access-control-allow-origin, content-type');
    header('Access-Control-Max-Age: 1');
    http_response_code(200);
} else {
    echo '{"error":"' .
        print_r($_SERVER) . '"}';
//    http_response_code(405);
}
