<?php
/**
 * Created by PhpStorm.
 * User: jssan
 * Date: 16/12/2018
 * Time: 09:48
 */

class Clube {
    private $id;
    private $endereco;
    private $contato;
    private $federacao;
    private $nome;
    private $sigla;
    private $numero;
    private $cnpj;
    private $conta;
    private $agencia;
    private $login;

    /**
     * Clube constructor.
     * @param $id
     * @param $endereco
     * @param $contato
     * @param $federacao
     * @param $nome
     * @param $sigla
     * @param $numero
     * @param $cnpj
     * @param $conta
     * @param $agencia
     * @param $login
     */
    public function __construct($id, $endereco, $contato, $federacao, $nome, $sigla, $numero, $cnpj, $conta, $agencia, $login) {
        $this->id = $id;
        $this->endereco = $endereco;
        $this->contato = $contato;
        $this->federacao = $federacao;
        $this->nome = $nome;
        $this->sigla = $sigla;
        $this->numero = $numero;
        $this->cnpj = $cnpj;
        $this->conta = $conta;
        $this->agencia = $agencia;
        $this->login = $login;
    }


    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEndereco() {
        return $this->endereco;
    }

    /**
     * @param mixed $endereco
     */
    public function setEndereco($endereco): void {
        $this->endereco = $endereco;
    }

    /**
     * @return mixed
     */
    public function getContato() {
        return $this->contato;
    }

    /**
     * @param mixed $contato
     */
    public function setContato($contato): void {
        $this->contato = $contato;
    }

    /**
     * @return mixed
     */
    public function getFederacao() {
        return $this->federacao;
    }

    /**
     * @param mixed $federacao
     */
    public function setFederacao($federacao): void {
        $this->federacao = $federacao;
    }

    /**
     * @return mixed
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome): void {
        $this->nome = $nome;
    }

    /**
     * @return mixed
     */
    public function getSigla() {
        return $this->sigla;
    }

    /**
     * @param mixed $sigla
     */
    public function setSigla($sigla): void {
        $this->sigla = $sigla;
    }

    /**
     * @return mixed
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero): void {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getCnpj() {
        return $this->cnpj;
    }

    /**
     * @param mixed $cnpj
     */
    public function setCnpj($cnpj): void {
        $this->cnpj = $cnpj;
    }

    /**
     * @return mixed
     */
    public function getConta() {
        return $this->conta;
    }

    /**
     * @param mixed $conta
     */
    public function setConta($conta): void {
        $this->conta = $conta;
    }

    /**
     * @return mixed
     */
    public function getAgencia() {
        return $this->agencia;
    }

    /**
     * @param mixed $agencia
     */
    public function setAgencia($agencia): void {
        $this->agencia = $agencia;
    }

    /**
     * @return mixed
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login): void {
        $this->login = $login;
    }


}