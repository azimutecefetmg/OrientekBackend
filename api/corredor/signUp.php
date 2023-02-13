<?php
/**
 * Created by PhpStorm.
 * User: josoe
 * Date: 26/01/19
 * Time: 15:33
 */

include_once('../../config.php');

$db = new DB();

$response = array("success" => false, "data" => "");


function formataDate($data) {
    $data = explode("/", $data);
    return $data[2] . '-' . $data[1] . '-' . $data[0];
}

function validaCPF($cpf) {
    // Verifica se um número foi informado
    if (empty($cpf)) {
        $response['data'] = "<span class='red-text'>ERRO: </span><span>Esse CPF não existe(10)</span>";
        return false;
    }

    // Elimina possivel mascara
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Verifica se o numero de digitos informados é igual a 11
    if (strlen($cpf) != 11) {
        $response['data'] = "<span class='red-text'>ERRO: </span><span>Esse CPF não existe(11)</span>" . $cpf;
        return false;

    }
    // Verifica se nenhuma das sequências invalidas abaixo
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cpf == '00000000000' ||
        $cpf == '11111111111' ||
        $cpf == '22222222222' ||
        $cpf == '33333333333' ||
        $cpf == '44444444444' ||
        $cpf == '55555555555' ||
        $cpf == '66666666666' ||
        $cpf == '77777777777' ||
        $cpf == '88888888888' ||
        $cpf == '99999999999') {
        $response['data'] = "<span class='red-text'>ERRO: </span><span>Esse CPF não existe(12)</span>" . $cpf;
        return false;
        /**
         *  Calcula os digitos verificadores para verificar se o
         *  CPF é válido
        */
    } else {

        for ($t = 9; $t < 11; $t++) {

            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf{$c} != $d) {
                $response['data'] = "<span class='red-text'>ERRO: </span><span>Esse CPF não existe(13)</span>" . $cpf;
                return false;
            }
        }

        return true;
    }
}


if (!empty($_POST['nome']) && !empty($_POST['dataNascimento']) && !empty($_POST['sexo']) && !empty($_POST['email']) && !empty($_POST['senhaum']) && !empty($_POST['senhadois']) && !empty($_POST['cpf'])) {

    if ($_POST['senhaum'] == $_POST['senhadois']) {


        $nome = $_POST['nome'];
        $dataNascimento = date('d/m/Y', strtotime($_POST['dataNascimento']));
        $sexo = $_POST['sexo'];
        $cpf = $_POST['cpf'];
        $idade = date("Y") - date('Y', strtotime($_POST['dataNascimento']));

        if ($idade <= 12) {
            $categoria = $sexo . 'IN';
        } elseif ($idade >= 13 && $idade <= 16) {
            $categoria = $sexo . 'JuvN';
        } elseif ($idade >= 17 && $idade <= 20) {
            $categoria = $sexo . 'JN';
        } elseif ($idade >= 21) {
            $categoria = $sexo . 'AN';
        }

        $login = $_POST['email'];
        $numCorredor = $_POST['numCorredor'];
        $pass = sha1($_POST['senhadois']);
        if (!(validaCPF($cpf))) {
            $response['data'] = '<span class="red-text">ERRO: </span><span>CPF inválido: ' . $cpf . '</span>';
            echo json_encode($response);
            exit;
        }
        $rua = $_POST['rua'];
        $numeroEndereco = $_POST['numeroEndereco'];
        $complemento = $_POST['complemento'];
        $bairro = $_POST['bairro'];
        $cidade = $_POST['cidade'];


        $uf = $_POST['uf'];

        $buscaUf = $db->query("SELECT idEstado FROM sistema_oriente.estado WHERE ufEstado='" . $uf . "';");
        $uf = $buscaUf->fetch_assoc()["idEstado"];
        $cep = $_POST['cep'];

        $tel1 = $_POST['tel1'];
        $tel2 = $_POST['tel2'];
        $email = $_POST['email'];

        $idClube = $_POST['idClube'];

        $query_verifica =  $db->query( "SELECT idCorredor FROM corredor WHERE loginCorredor = '$login'");
        $verificacao = $query_verifica->num_rows;

        if ($verificacao > 0) {
            //TODO fazer retorno das respostas de erro para o toast
            $response['data'] = "<span class='red-text'>Já existe um usuário com esse email!</span>";
        } else {
            $queryBairro =  $db->query( "INSERT INTO bairro(idBairro, nomeBairro, idCidade) VALUES ('', '$bairro', $cidade);");
            if ($queryBairro) {
                $idBairro = $db->conn->insert_id;
                $queryRua =  $db->query( "INSERT INTO rua(idRua, nomeRua, idBairro) VALUES ('', '$rua', '$idBairro');");
                if ($queryRua) {
                    $idRua = $db->conn->insert_id;

                    $queryComplemento =  $db->query( "INSERT INTO complemento(idComplemento, nomeComplemento) VALUES ('', '$complemento');");

                    if ($queryComplemento) {
                        $idComplemento = $db->conn->insert_id;

                        $queryEndereco =  $db->query( "INSERT INTO endereco(idEndereco, numeroEndereco, CEPEndereco, idRua, idComplemento) VALUES ('', '$numeroEndereco', '$cep', '$idRua', '$idComplemento');");

                        if ($queryEndereco) {
                            $idEndereco = $db->conn->insert_id;

                            $queryContato =  $db->query( "INSERT INTO contato(idContato, Telefone1, email) VALUES ('', '$tel1', '$email');");

                            if ($queryContato) {
                                $idContato = $db->conn->insert_id;

                                $queryCorredor =  $db->query( "INSERT INTO corredor(idCorredor, nomeCorredor, sexoCorredor, categoriaCorredor, dataNascimentoCorredor, idContato, numCorredor, CPFCorredor, idEndereco, idClube, loginCorredor, senhaCorredor) VALUES ('','$nome', '$sexo', '$categoria', '$dataNascimento', '$idContato', '$numCorredor', '$cpf', '$idEndereco', '$idClube', '$login', '$pass');");
                                if ($queryCorredor) {
                                    $response['success'] = true;
                                    $response['data'] = "<span class='red-text'>ERRO: </span><span>Cadastro feito com sucesso</span>";
                                } else {
                                    $response["data"] = "<span class='red-text'>ERRO: </span><span>Erro ao registrar corredor(-1)</span>";
                                }
                            } else {
                                $response["data"] = "<span class='red-text'>ERRO: </span><span>Erro ao registrar contato(1)</span>";
                            }
                        } else {
                            $response["data"] = "<span class='red-text'>ERRO: </span><span>Erro ao registrar endereco(2)</span>";
                        }
                    } else {
                        $response["data"] = "<span class='red-text'>ERRO: </span><span>Erro ao registrar endereco(3)</span>";
                    }
                } else {
                    $response["data"] = "<span class='red-text'>ERRO: </span><span>Erro ao registrar rua(4)</span>";
                }
            } else {
                $response["data"] = "<span class='red-text'>ERRO: </span><span>Erro ao registrar bairro(5)</span>";
            }
        }
        $db->conn->close();
    } else {
        $response['data'] = "<span class='yellow-text'>ERRO: </span><span>As senhas n&atilde;o coincidem!</span>";
    }
} else {
    $response['data'] = "<span class='yellow-text'>ERRO: </span><span>Todos os campos são obrigatórios</span>";
}

echo json_encode($response);
