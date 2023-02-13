<?php
include_once('../../config.php');


$db = new DB();

function preparaDatas($evento) {
    $evento['dataEvento'] = str_replace('-', '/', $evento['dataEvento']);
    $evento['inscricaoEvento'] = str_replace('-', '/', $evento['inscricaoEvento']);
    $evento['pagamentoEvento'] = str_replace('-', '/', $evento['pagamentoEvento']);
    return $evento;
}

$UId = "";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests


    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);


} else {
    /**
     * A api funcionaeatamente aqui
     */
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
        $query = "SELECT tokenExpiration, idCorredor FROM sistema_oriente.corredor WHERE authToken='" . $_SERVER['HTTP_AUTHORIZATION'] . "';";
        $res = $db->query($query);
        if (!$res) {
            echo $db->error();
            exit();
        }
        if ($res->num_rows > 0) {
            $res = $res->fetch_assoc();
            $expiration = $res['tokenExpiration'];
            if (strtotime($expiration) > time()) {
                echo "token {$_SERVER['HTTP_AUTHORIZATION']} expired";
                http_response_code(401);
                exit;
            } else {
                $UId = $res['idCorredor'];
            }
        } else {
            echo "token {$_SERVER['HTTP_AUTHORIZATION']} dont existis";
            http_response_code(401);
        }
    } else {
        http_response_code(403);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        /**
         *  BUSCAR EVENTO
         *
         */
        if (isset($_GET['campo']) || isset($_GET['estado'])) {
            $campo = empty($_GET['campo']) ? "" : $_GET['campo'];
            $estado = empty($_GET['estado']) ? "" : $_GET['estado'];

            $campos = explode('+', $campo);
            $meses = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setemro", "Outubro", "Novembro", "Dezembro");

            // matriz de entrada
            $entrada = array('ä', 'ã', 'à', 'á', 'â', 'ê', 'ë', 'è', 'é', 'ï', 'ì', 'í', 'ö', 'õ', 'ò', 'ó', 'ô', 'ü', 'ù', 'ú', 'û', 'À', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ç', 'Ç', '(', ')', ',', ';', ':', '|', '!', '"', '#', '$', '%', '&', '/', '=', '?', '~', '^', '>', '<', 'ª', 'º');

            // matriz de saída
            $saida = array('a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'A', 'A', 'E', 'I', 'O', 'U', 'n', 'n', 'c', 'C', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

            $carac_invalidos = false;

            $injection = str_replace($entrada, $saida, $campo);
            if ($injection != $campo) {
                $carac_invalidos = true;
                $cod_erro = 102;
            }

            $injection = str_replace($entrada, $saida, $estado);
            if ($injection != $estado) {
                $carac_invalidos = true;
                $cod_erro = 102;
            }

            if (!$carac_invalidos) {
                $query = "SELECT * FROM sistema_oriente.evento WHERE inscricaoEvento >= NOW() AND ";
                if (sizeof($campos)) {
                    $query .= " nomeEvento LIKE '%{$campos[0]}%'";
                    for ($i = 1; $i < sizeof($campos); $i++) {
                        $query .= " OR nomeEvento LIKE '%{$campos[$i]}%' ";
                    }
                }
                // TODO Adicionar filtros por estado e categoria
                $query .= ";";
                $res = $db->query($query);
                //TODO Refazer ou corrigir o algoritimo de busca
                if (!$res) {
                    echo $db->error();
                }
                $rows = $res->num_rows;
                if ($rows > 0) {
                    $r = array();
                    while ($row = $res->fetch_assoc()) {
                        $qEndereco = "SELECT
 endereco.CEPEndereco,
 cidade.nomeCidade,
 bairro.nomeBairro,
 estado.nomeEstado,
 estado.idEstado,
 rua.nomeRua
   FROM 
    sistema_oriente.endereco,
    sistema_oriente.estado,
    sistema_oriente.bairro,
    sistema_oriente.rua,
    sistema_oriente.cidade 
  WHERE 
    endereco.idRua= rua.idRua AND
    rua.idBairro = bairro.idBairro AND 
    bairro.idCidade = cidade.idCidade AND
    cidade.idEstado = estado.idEstado";
                        $endereco = $db->query($qEndereco);
                        if ($endereco) {
                            $row = array_merge(preparaDatas($row), $endereco->fetch_assoc());
                        } else {
                            echo $db->error();
                        }
                        array_push($r, $row);
                    }
                    echo json_encode($r);
                    exit;
                }
                echo "[]";
            }
        } else if (isset($_GET['id'])) {
            $query = "SELECT * FROM sistema_oriente.evento WHERE idEvento ={$_GET['id']};";
            $res = $db->query($query);
            if ($res) {
                $qClube = "SELECT clube.nomeClube, clube.contaClube, clube.agenciaClube FROM sistema_oriente.clube, sistema_oriente.evento WHERE evento.idClube=clube.idClube AND evento.idEvento={$_GET['id']};";
                $qEndereco = "SELECT
 endereco.CEPEndereco,
 cidade.nomeCidade,
 bairro.nomeBairro,
 estado.nomeEstado,
 estado.idEstado,
 rua.nomeRua
   FROM 
    sistema_oriente.endereco,
    sistema_oriente.estado,
    sistema_oriente.bairro,
    sistema_oriente.rua,
    sistema_oriente.cidade 
  WHERE 
    endereco.idRua= rua.idRua AND
    rua.idBairro = bairro.idBairro AND 
    bairro.idCidade = cidade.idCidade AND
    cidade.idEstado = estado.idEstado";
                $queryParticipacao = "SELECT pagoParticipacao, comprovante,tempoParticipacao FROM sistema_oriente.participacao WHERE idCorredor={$UId} AND idEvento={$_GET['id']};";
                $inscrito = $db->query($queryParticipacao);
                if (!$inscrito) die($db->error());
                $endereco = $db->query($qEndereco);
                if (!$endereco) echo $db->error();
                $cl = $db->query($qClube);
                if (!$cl) echo $db->error();
                if ($endereco->num_rows && $res->num_rows) {
                    $r = array_merge(preparaDatas($res->fetch_assoc()), $endereco->fetch_assoc());
                    $r = array_merge($r, $cl->fetch_assoc());
                    if ($inscrito->num_rows > 0) {
                        $r = array_merge($r, $inscrito->fetch_assoc());
                    }
                    echo json_encode($r);
                }
            } else {
                echo $db->error();
            }
        } else {
            //TODO Implementar mais detalhes da inscrção aqui
            $query = "SELECT evento.*, participacao.pagoParticipacao FROM sistema_oriente.evento, sistema_oriente.participacao WHERE participacao.idCorredor={$UId} AND participacao.idEvento = evento.idEvento;";
            $res = $db->query($query);
            if (!$res) die($db->error());
            if ($res->num_rows > 0) {
                $r = array();
                while ($row = $res->fetch_assoc()) {
                    array_push($r, $row);
                }
                echo json_encode($r);
            } else json_encode(array());
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        /**
         *  INSCRIÇÃO EM EVENTO
         */
        header('Content-Type: application/json');
        $r = false;
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['evento'])) {
            $queryPreco = "SELECT precoEvento FROM sistema_oriente.evento WHERE idEvento={$data['evento']}";
            $queryPreco = $db->query($queryPreco);
            if (!$queryPreco) die ($db->error());
            $queryPreco = $queryPreco->fetch_assoc();
            if ($queryPreco['precoEvento'] > 0) {
                $db->query("INSERT INTO participacao (idEvento, idCorredor, tempoParticipacao, pontuacaoParticipacao, chegadaParticipacao, partidaParticipacao, pagoParticipacao) VALUES ('{$data['evento']}', '$UId', 0, 0, '0', 0, 0);");
            } else {
                $db->query("INSERT INTO participacao (idEvento, idCorredor, tempoParticipacao, pontuacaoParticipacao, chegadaParticipacao, partidaParticipacao, pagoParticipacao) VALUES ('{$data['evento']}', '$UId', 0, 0, '0', 0, 1);");
            }
            $r = true;

        } else if (isset($data['participacao'])) {
            $query = "UPDATE sistema_oriente.participacao SET pagoParticipacao=1 WHERE idParticipa={$data['participacao']}";
            if ($db->executeUpdate($query, $UId)) {
                $r = true;
            } else {
                $r = false;
                echo $db->error();
            }
            // TODO APROVAR PARTICIPACAO
        } else if (isset($_FILES['file'])) {
            // TODO Verfificar integridade e segurança do arquivo
            $fileName = $_FILES['file']['name'];
            $ext = @end(explode('.', $fileName));
            $uniqueName = rand(100, 999) . '_' . time();
            $uploadDir = 'comprovantes/';
            // Nome = diretorio/ timestamp do upload _ Numero de 3 digitos aleatório. extensão
            $newfilename = $uploadDir . $uniqueName . '.' . $ext;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $newfilename)) {
                $sql = $db->query("UPDATE sistema_oriente.participacao SET pagoParticipacao=1, comprovante='$newfilename' WHERE idCorredor=$UId  and idEvento= {$_POST['evento']} ;");
                echo $db->error();
                if ($sql) {
                    $r = true;
                } else {
                    $r = false;
                }
            } else {
                $r = false;
            }

        }
        echo '{"ok": ' . $r . '}';
    }
}
