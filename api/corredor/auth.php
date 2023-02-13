<?php
/**
 * Created by PhpStorm.
 * User: josoe
 * Date: 24/01/19
 * Time: 09:58
 */
include_once('../../config.php');
$DB = new DB();
header('Content-Type: application/json');

function testaLogin($email, $senha) {
    $DB = new DB();
    $query = "SELECT senhaCorredor,idCorredor FROM sistema_oriente.corredor WHERE loginCorredor='{$email}'";
    $r = $DB->query($query);
    if ($r) {
        if ($r->num_rows) {
            $user = $r->fetch_assoc();
            if (sha1($senha) === $user['senhaCorredor']) {
                return $user['idCorredor'];
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
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo '{error:"' . json_last_error_msg() . '"}';
    }
    if (isset($data['email']) && isset($data['senha'])) {
        /**
         * LOGIN
         */
        $login = testaLogin($data['email'], $data['senha']);
        if ($login) {
            $cstrong = True;
            // TODO Configure token expiration
            $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
            $time = time() + 60 * 60 * 24 * 15;
            $r = $DB->query("UPDATE sistema_oriente.corredor SET corredor.authToken='${token}',tokenExpiration={$time} WHERE idCorredor={$login}");
            if ($r) {
                $r = $DB->query("SELECT idCorredor FROM sistema_oriente.corredor WHERE authToken='{$token}';");
                if (!$r) {
                    echo $DB->error();
                }
                $r = $r->fetch_assoc();
                echo '{"token":"' . $token . '","expiration":"' . $time . '", "id":"' . $r['idCorredor'] . '"}';
            } else {
                echo '{"error":"' . $DB->error() . '"}';
            }
        }
    } else if (isset($data['token']) && isset($data['id'])) {
        /**
         * Retornar dados uteis do usuário
         */
        $query = "SELECT * FROM sistema_oriente.corredor WHERE authToken='{$data['token']}' AND  idCorredor={$data['id']} ;";
        $res = $DB->query($query);
        if (!$res) die($DB->error());
        if ($res->num_rows === 1) {
            echo json_encode($res->fetch_assoc());
        } else {
            echo false;
        }
    } else if ($data['token']) {
        // vefica autorização
        // TODO Validar expiração do token
        $r = $DB->query("SELECT idCorredor, tokenExpiration FROM sistema_oriente.corredor WHERE authToken={$data['token']}");
        if ($r && $r->num_rows > 0) {
            $r = $r->fetch_assoc();
            if ($r['idCorredor'] === $data['id']) {
                if ($r['tokenExpiration'] < time()) {
                    echo '{"id": "' . $r["idClube"] . '"}';
                }
            }
            //TODO Definir resposta para token epirado
        }
    } else {
        http_response_code(401);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: "*"');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: content-type');
    header('Access-Control-Max-Age: 1');
    http_response_code(200);
}
