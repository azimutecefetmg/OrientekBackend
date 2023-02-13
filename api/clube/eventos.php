<?php
/**
 * Created by PhpStorm.
 * User: josoe
 * Date: 29/01/19
 * Time: 14:38
 */
include_once('../../config.php');

header('Access-Control-Allow-Origin: "*"');
header('Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: access-control-allow-origin, Authorization, content-type');
header('Access-Control-Max-Age: 1');

header('Content-Type: application/json');

$db = new DB();

function formataDate($data) {
    $data = explode("/", $data);
    return $data[2] . '-' . $data[1] . '-' . $data[0];
}

function preparaDatas($evento) {
    $evento['dataEvento'] = str_replace('-', '/', $evento['dataEvento']);
    $evento['inscricaoEvento'] = str_replace('-', '/', $evento['inscricaoEvento']);
    $evento['pagamentoEvento'] = str_replace('-', '/', $evento['pagamentoEvento']);
    return $evento;
}

$UId = "";

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $query = "SELECT tokenExpiration, idClube FROM sistema_oriente.clube WHERE authToken='" . $_SERVER['HTTP_AUTHORIZATION'] . "';";
    $res = $db->query($query);
    if (!$res) {
        echo $db->error();
        exit();
    }
    if ($res->num_rows > 0) {
        $res = $res->fetch_assoc();
        $expiration = $res['tokenExpiration'];
        if (strtotime($expiration) > time()) {
            http_response_code(401);
            exit;
        } else {
            $UId = $res['idClube'];
        }
    } else {
        http_response_code(401);
        exit;
    }
} else {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Cadastra evento
     */
    $response = array('success' => false, 'data' => '');

    if (!empty($_POST['nomeEvento']) && !empty($_POST['precoEvento']) && !empty($_POST['horarioEvento']) && !empty($_POST['dataEvento']) && !empty($_POST['inscricaoEvento']) && !empty($_POST['pagamentoEvento']) && !empty($_POST['rua']) && !empty($_POST['numeroEndereco']) && !empty($_POST['bairro']) && !empty($_POST['cidade']) && !empty($_POST['estado']) && !empty($_POST['cep'])) {
        $nomeEvento = htmlentities($_POST['nomeEvento']);
        $precoEvento = str_replace(",", ".", $_POST['precoEvento']);
        $precoEvento = preg_replace('/[^0-9\.]/', '', $precoEvento);
        $horarioEvento = $_POST['horarioEvento'];
        $dataEvento = formataDate($_POST['dataEvento']);
        $inscricaoEvento = formataDate($_POST['inscricaoEvento']);
        $pagamentoEvento = formataDate($_POST['pagamentoEvento']);

        $rua = $_POST['rua'];
        $numeroEndereco = $_POST['numeroEndereco'];
        $complemento = empty($_POST['complemento']) ? null : $_POST['complemento]'];
        $bairro = $_POST['bairro'];
        $cidade = $_POST['cidade'];
        $uf = $_POST['estado'];
        $cep = preg_replace('/[^0-9]/', '', $_POST['cep']);

        $dataEvento = implode("-", array_reverse(explode("/", $dataEvento)));;
        $inscricaoEvento = implode("-", array_reverse(explode("/", $inscricaoEvento)));;
        $pagamentoEvento = implode("-", array_reverse(explode("/", $pagamentoEvento)));;

        $query_verifica = $db->query("SELECT idEvento FROM evento WHERE nomeEvento = '$nomeEvento' AND dataEvento = '$dataEvento'");
        $verificacao = $query_verifica->num_rows;

        if ($verificacao > 0) {
            $response['data'] = "<span class='red-text'>ERRO:</span>Esse evento já foi cadastrado";
        } else {
            $queryBairro = $db->query("INSERT INTO  bairro(idBairro, nomeBairro, idCidade) VALUES ('', '$bairro', '$cidade');");

            if ($queryBairro) {
                $idBairro = $db->conn->insert_id;

                $queryRua = $db->query("INSERT INTO rua(idRua, nomeRua, idBairro) VALUES ('', '$rua', '$idBairro');");

                if ($queryRua) {
                    $idRua = $db->conn->insert_id;

                    $queryComplemento = $db->query("INSERT INTO complemento(idComplemento, nomeComplemento) VALUES ('', '$complemento');");

                    if ($queryComplemento) {
                        $idComplemento = $db->conn->insert_id;

                        $queryEndereco = $db->query("INSERT INTO endereco(idEndereco, numeroEndereco, CEPEndereco, idRua, idComplemento) VALUES ('', '$numeroEndereco', '$cep', '$idRua', '$idComplemento');");

                        if ($queryEndereco) {
                            $idEndereco = $db->conn->insert_id;

                            $queryEvento = $db->query("INSERT INTO evento(idEvento, idEndereco, idClube, precoEvento, nomeEvento, horarioEvento, dataEvento, inscricaoEvento, pagamentoEvento) VALUES ('', '$idEndereco', '$UId', '$precoEvento', '$nomeEvento', '$horarioEvento', '$dataEvento', '$inscricaoEvento', '$pagamentoEvento');");
                            $idEvento = $db->conn->insert_id;
                            if ($queryEvento) {
                                $queryAgrupamento = $db->query("INSERT INTO tabelacorredor(nomeTabela) VALUES ('$nomeEvento')");
                                $idAgrupamento = $db->conn->insert_id;
                                $updateEvento = $db->query("UPDATE evento SET idTabela = '$idAgrupamento' WHERE idEvento = '$idEvento'");
                                if ($updateEvento) {
                                    $response['success'] = true;
                                    $response['data'] = "Evento cadastrado com sucesso!";
                                } else {
                                    $response['data'] = '<span class="red-text">ERRO:</span> Erro de conectividade';
                                }
                            } else {

                                $response['data'] = '<span class="red-text">ERRO:</span> Erro de conectividade';
                            }

                        } else {
                            $response['data'] = '<span class="red-text">ERRO:</span> Erro ao resgistrar endereço(0)';
                        }
                    } else {
                        $response['data'] = '<span class="red-text">ERRO:</span> Erro ao resgistrar endereço(1)';
                    }
                } else {
                    $response['data'] = '<span class="red-text">ERRO:</span> Erro ao resgistrar rua(2)';
                }
            } else {
                $response['data'] = '<span class="red-text">ERRO:</span> Erro ao resgistrar bairro(3)';
            }
        }
    } else {
        $response['data'] = '<span class="red-text">ERRO:</span> Todos os campos são obrigatórios' . var_dump($_POST);
    }
    echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    header('Content-Type: application/json');
    $r = false;
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['participacao'])) {
        $query = "UPDATE sistema_oriente.participacao SET pagoParticipacao=1 WHERE idParticipa={$data['participacao']}";
        if ($db->executeUpdate($query, $UId)) {
            $r = true;
        } else {
            $r = false;
            echo $db->error();
        }
        // TODO Enviar email ou notificação avisando o corredor
    } else {
        var_dump($data);
        http_response_code(400);
    }
    echo '{"ok": ' . $r . '}';
    exit(0);

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        if (isset($_GET['participacoes'])) {
            $queryParticipacoes = "SELECT * FROM sistema_oriente.participacao WHERE idEvento={$_GET['id']}";
            $queryCorredores = "SELECT 
                                       corredor.idCorredor, idClube, idEndereco, idContato, nomeCorredor, categoriaCorredor, numCorredor, dataNascimentoCorredor, sexoCorredor, CPFCorredor, loginCorredor, valido
 FROM sistema_oriente.corredor,sistema_oriente.participacao WHERE participacao.idCorredor=corredor.idCorredor AND participacao.idEvento={$_GET['id']}";
            $res = $db->query($queryParticipacoes);
            $resCorredores = $db->query($queryCorredores);
            if (!$res) die($db->error());
            if (!$resCorredores) die($db->error());
            $r = array('participacao' => array(), 'corredor' => array());
            while ($row = $res->fetch_assoc()) {
                array_push($r['participacao'], $row);
            }
            while ($row = $resCorredores->fetch_assoc()) {
                array_push($r['corredor'], $row);
            }
            echo json_encode($r);
        }
    } else {
        // Retornar todos os eventos
        //RETORNAR NUMERO DE PAGAMENTOS
        $query = "SELECT evento.* FROM sistema_oriente.evento WHERE  evento.idClube={$UId};";
        $res = $db->query($query);
        if (!$res) die($db->error());
        $r = array();
        while ($row = $res->fetch_assoc()) {
            $queryParticipacoes = "SELECT count(idEvento) AS conta FROM sistema_oriente.participacao WHERE participacao.idEvento ={$row['idEvento']};";
            $queryParticipacoes = $db->query($queryParticipacoes);
            if (!$queryParticipacoes) die($db->error());
            $row['participacoes'] = $queryParticipacoes->fetch_assoc()['conta'];
            array_push($r, $row);
        }
        echo json_encode($r);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

} else {
    //bad request
    var_dump($_SERVER);
    http_response_code(400);
}
