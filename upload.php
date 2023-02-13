<?php

include "Clube.php";
include_once('config.php');

header('Access-Control-Allow-Origin: "*"');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: access-control-allow-origin, Authorization, content-type');
header('Access-Control-Max-Age: 1');

$db = new DB();
$UId = "";

if($_SERVER['REQUEST_METHOD']==='OPTIONS'){
    http_response_code(200);
    exit;
}

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $query = "SELECT tokenExpiration, idClube FROM sistema_oriente.clube WHERE authToken='{$_SERVER["HTTP_AUTHORIZATION"]}';";
    $res = $db->query($query);
    if (!$res) {
        echo $db->error();
        exit();
    }
    if ($res->num_rows > 0) {
        $res = $res->fetch_assoc();
        $expiration = $res['tokenExpiration'];
        if ($expiration > time()) {
            $UId = $res['idClube'];
        } else {
            http_response_code(401);
            exit;
        }
    } else {
        http_response_code(401);
        exit;
    }
} else {
    http_response_code(403);
    exit;
}


class Upload {
    private $TABELAS;
    private $connLocal;

    function log(string $data) {
        $t = date("d/m/y/ h:m:s");
        $data = "[upload.php][{$t}]:\n{$data}\n";
        file_put_contents("dblog.log", $data);
    }

    function getLastModifiedServer($name) {
        return '2018-10-09 12:00:00';
    }

    /**
     * Função para verificar se as tabelas foram modificadas desde a ultima sincronização
     * @param $name nome da tabela
     * @return bool|int|mysqli_result
     */
    function getLastModifiedLocal($name) {
        $connLocal = new mysqli("localhost", "root", "", "sistema_oriente");
        $query = "SELECT last_edit FROM sistema_oriente.version_control WHERE data_name='${name}'";
        $result = $connLocal->query($query);
        if ($result && $result->num_rows === 0) {
            $time = date("y-m-d h:m:s", time());
            $connLocal->query("INSERT INTO sistema_oriente.version_control(data_name, last_edit) VALUES('{$name}','{$time}')");
            return $time;
        }
        if ($result) {
            return $result->fetch_all()[0][0];
        } else {
            $this->log($this->connLocal->error);
            return $result;
        }
    }

    function fetch($result) {
        $r = array();
        while ($row = $result->fetch_assoc()) {
            array_push($r, $row);
        }
        return $r;
    }


    function getEndereco($idEndereco) {
        $query = "SELECT idComplemento,idRua FROM sistema_oriente.endereco WHERE idEndereco={$idEndereco}";
        $rows = $this->connLocal->query($query);
        $result = null;
        if ($rows && $rows->num_rows > 0) {
            $result = array("endereco" => $rows->fetch_assoc());
            //TODO retornar o endereço de forma literal ou esquematico(depende de como vai ser usado no programa
        } else {
            $this->log($this->connLocal->error);
            return $rows;
        }
    }

    function getParticipacao($idClube) {
        $query = "SELECT participacao.*
FROM sistema_oriente.participacao,
     sistema_oriente.clube,
     sistema_oriente.evento
WHERE clube.idClube={$idClube} AND clube.idClube = evento.idClube
  AND evento.idEvento = participacao.idEvento";
        $r = $this->connLocal->query($query);
        if (!$r) {
            $this->log($this->connLocal->error);
        }
        return $r;
    }

    function getEventos($idClube) {
        $query = "SELECT * FROM sistema_oriente.evento WHERE idClube={$idClube}";
        $result = $this->connLocal->query($query);
        return $result;
    }

    function __construct($id) {
        $this->TABELAS = array("evento", "corredor", "participacao", "tabelacorredor", "clube", "agrupamento");
        $this->connLocal = new mysqli("localhost", "root", "", "sistema_oriente");
        $this->connLocal->set_charset("utf8");
        $idClube = $id;
        $clube = $this->connLocal->query("SELECT idClube, idEndereco, idContato, idFederacao, nomeClube, siglaClube, numeroClube, CNPJClube, contaClube, agenciaClube, loginClube FROM sistema_oriente.clube WHERE idClube={$idClube}");
        $response = array();
        if ($clube) {
            if ($clube->num_rows === 0) {
                die(json_encode(array("erro" => 404)));
            } else {
                $clube = $clube->fetch_assoc();
                $response['clube'] = [$clube];
            }
        } else {
            die(json_encode(array("erro" => 500, "stack" => $this->connLocal->error)));
        }
        //Busca de EVENTOS
        $eventos = $this->getEventos($clube["idClube"]);
        if ($eventos) {
            $response["evento"] = $this->fetch($eventos);
        } else {
            die(json_encode(array("status" => 500)));
        }
        //BUSCA DE PARTICIPAÇOES
        $participacoes = $this->getParticipacao($clube["idClube"]);
        if ($participacoes) {
            if ($participacoes->num_rows > 0) {
                $response["participacao"] = $this->fetch($participacoes);
            }
        } else json_encode(array("erro" => 500));


        //BUSCA DE CORREDORES
        $q = "
SELECT DISTINCT corredor.idCorredor,
                corredor.idClube,
                corredor.idEndereco,
                corredor.idContato,
                corredor.nomeCorredor,
                corredor.categoriaCorredor,
                corredor.numCorredor,
                corredor.dataNascimentoCorredor,
                corredor.sexoCorredor,
                corredor.CPFCorredor,
                corredor.loginCorredor
FROM participacao,
     corredor,
     clube,
     evento
WHERE clube.idClube = {$clube["idClube"]} AND clube.idClube = evento.idEvento
        AND evento.idEvento = participacao.idEvento
        AND participacao.idCorredor = corredor.idCorredor OR corredor.idClube = {$clube["idClube"]};
";
        $corredores = $this->connLocal->query($q);

        if ($corredores) {
            if ($corredores->num_rows) {
                $response["corredor"] = $this->fetch($corredores);
            }
        } else {
            die($this->connLocal->error);
        }
        $resString = json_encode($response);
        if (!$resString) {
            echo "DEU RUIM";
            echo json_last_error_msg();
        }
        echo utf8_encode(json_encode($response));
    }
}

//selecionar todos eventos por clube
//selecionar todos particiapantes por evento
new Upload($UId);

