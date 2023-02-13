<?php
/**
 * Created by PhpStorm.
 * User: LucasAugustoNiessSoa
 * Date: 31/01/2017
 * Time: 01:37
 */

include_once('../../config.php');


$db = new DB();

$nomeClube = $_POST['nomeClube'];
$siglaClube = $_POST['siglaClube'];
$numeroClube = $_POST['numeroClube'];
$CNPJ = $_POST['cnpj'];
$contaClube = $_POST['conta'];
$agenciaClube = $_POST['agencia'];
$loginClube = $_POST['email'];
$senhaClube = sha1($_POST['senhadois']);


$rua = $_POST['rua'];
$numeroEndereco = $_POST['numeroEndereco'];
$complemento = $_POST['complemento'];
$bairro = $_POST['bairro'];
$cidade = $_POST["cidade"];;
$uf = mysqli_fetch_assoc($db->query("SELECT idEstado FROM sistema_oriente.estado WHERE ufEstado='" . $_POST['uf'] . "';"))['idEstado'];
$cep = $_POST['cep'];

$tel1 = $_POST['tel1'];
$tel2 = $_POST['tel2'];
$email = $_POST['email'];

$nomeFederacao = $_POST['nomeFederacao'];
$siglaFederacao = $_POST['siglaFederacao'];


if (!empty($_POST['nomeClube']) && !empty($_POST['rua']) && !empty($_POST['numeroEndereco']) && !empty($_POST['bairro']) && !empty($_POST['cidade']) && !empty($_POST['uf']) && !empty($_POST['cep']) && !empty($_POST['tel1']) && !empty($_POST['tel2']) && !empty($_POST['email']) && !empty($_POST['senhaum']) && !empty($_POST['senhadois']) && !empty($_POST['nomeFederacao'])) {
    //defini estas variaveis como obrigatórias no cadastro, para tirar obrigatoreidade basta remover do if acima

    if ($_POST['senhaum'] == $_POST['senhadois']) {

        $query_verifica = $db->query("SELECT idClube FROM clube WHERE loginClube = '$loginClube'");
        $verificacao = $query_verifica->num_rows;
        $response = array("success" => true, "data" => "");
        if ($verificacao > 0) {
            $response["success"] = false;
            $response["data"] = "<span class='red-text'>ERRO: </span><span>J&aacute; existe um clube com esse email!</span>";
        } else {
            $queryBairro = $db->query("INSERT INTO  bairro(nomeBairro, idCidade) VALUES ( '$bairro', '$cidade');");

            if ($queryBairro) {
                $idBairro = $db->conn->insert_id;

                $queryRua = $db->query("INSERT INTO rua( nomeRua, idBairro) VALUES ( '$rua', '$idBairro');");

                if ($queryRua) {
                    $idRua = $db->conn->insert_id;

                    $queryComplemento = $db->query("INSERT INTO complemento(idComplemento, nomeComplemento) VALUES ('', '$complemento');");

                    if ($queryComplemento) {
                        $idComplemento = $db->conn->insert_id;

                        $queryEndereco = $db->query("INSERT INTO endereco(idEndereco, numeroEndereco, CEPEndereco, idRua, idComplemento) VALUES ('', '$numeroEndereco', '$cep', '$idRua', '$idComplemento');");

                        if ($queryEndereco) {
                            $idEndereco = $db->conn->insert_id;

                            $queryContato = $db->query("INSERT INTO contato(idContato, Telefone1, Telefone2, email) VALUES ('', '$tel1', '$tel2', '$email');");

                            if ($queryContato) {
                                $idContato = $db->conn->insert_id;

                                $queryFederacao = $db->query("INSERT INTO federacao (idFederacao, nomeFederacao, siglaFederacao) VALUES ('', '$nomeFederacao', '$siglaFederacao');");

                                if ($queryFederacao) {
                                    $idFederacao = $db->conn->insert_id;
                                    $time = date('y-m-d h:m:s',time());
                                    $queryClube = $db->query("INSERT INTO clube (idClube, idEndereco, idContato, idFederacao, nomeClube, siglaClube, numeroClube, CNPJClube, contaClube, agenciaClube, loginClube, senhaClube,lastEdited) VALUES ('', '$idEndereco', '$idContato', '$idFederacao', '$nomeClube', '$siglaClube', '$numeroClube', '$CNPJ', '$contaClube', '$agenciaClube', '$loginClube', '$senhaClube','$time');");

                                    if ($queryClube) {
                                        $response["success"] = true;
                                        $response["data"] = "<span>Entrando</span>";

                                    } else {
                                        //erro clube
                                        $response["success"] = false;
                                        $response["data"] = "<span>Ocorreu um erro no cadastro do clube! Tente novamente.</span>";
                                    }

                                } else {
                                    //erro federacao
                                    $response["success"] = false;
                                    $response["data"] = "<span class='red-text'>ERRO: </span><span>Ocorreu um erro no cadastro da federação! Tente novamente.</span>";
                                }
                            } else {
                                //erro contato
                                $response["success"] = false;
                                $response["data"] = "<span class='red-text'>ERRO: </span><span>Ocorreu um erro no cadastro do contato! Tente novamente.</span>";
                            }
                        } else {
                            //erro endereco
                            $response["success"] = false;
                            $response["data"] = "<span class='red-text'>ERRO: </span><span>Ocorreu um erro no cadastro do endereco! Tente novamente.</span>";
                        }
                    } else {
                        //erro complemento
                        $response["success"] = false;
                        $response["data"] = "<span class='red-text'>ERRO: </span><span>Ocorreu um erro no cadastro do complemento! Tente novamente.</span>";
                    }
                } else {
                    //erro rua
                    $response["success"] = false;
                    $response["data"] = "<span class='red-text'>ERRO: </span><span>Ocorreu um erro no cadastro da rua! Tente novamente.</span>";
                }
            } else {
                //erro bairro
                $response["success"] = false;
                $response["data"] = "<span class='red-text'>ERRO: </span><span>Ocorreu um erro no cadastro do bairro! Tente novamente. </span>";
            }
        }
    } else {
        $response["success"] = false;
        $response["data"] = "<span class='red-text'>ERRO: </span><span>As senhas n&atilde;o coincidem!</span>";
    }
} else {
    $response["success"] = false;
    $response["data"] = "<span class='red-text'>ERRO: </span><span>Todos os campos s&atilde;o obrigat&oacute;rios!</span>";
}
echo json_encode($response);

?>
