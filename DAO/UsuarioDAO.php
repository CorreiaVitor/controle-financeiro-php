<?php

require_once '../DAO/Conexao.php';
require_once '../DAO/UtilDAO.php';

class UsuarioDAO extends Conexao
{

    private $conexao;

    public function __construct()
    {
        $this->conexao = parent::retornarConexao();
    }

    public function CarregarMeusDados()
    {

        $comando_sql = 'SELECT nome_usuario, email_usuario FROM tb_usuario WHERE id_usuario = ? ';

        $sql = new PDOStatement;
        $sql = $this->conexao->prepare($comando_sql);
        $sql->bindValue(1, UtilDAO::UsuarioLog());

        //aqui você irá definir o modo de busca (setFetchMode) que será por busca associada (FETCH_ASSOC) a função setFetchMode elimina os index do array 
        $sql->setFetchMode(PDO::FETCH_ASSOC);

        //aqui o comando será executado 
        $sql->execute();

        //fetchAll retorna o que foi configurado no comando sql pelo PHP e retorna a pesquisa do banco de dados.
        return $sql->fetchAll();

    }

    public function GravarDadosUsuario($nome, $email)
    {
       if(empty($nome) || empty($email)){
        return 1;
       }

       if($this->VerificarEmailAlteracao($email) != 0){
        return -5;
       }

       $conexao = parent::retornarConexao();

       $comando_sql = 'UPDATE tb_usuario 
                           SET nome_usuario = ?, email_usuario = ?
                       WHERE id_usuario = ? ';

       $sql = new PDOStatement();

       $sql = $conexao->prepare($comando_sql);

       $i = 1;
       $sql->bindValue($i++, $nome);
       $sql->bindValue($i++, $email);
       $sql->bindValue($i++, UtilDAO::UsuarioLog());

       try{
        $sql->execute();
        return 1;
       }catch(Exception $ex){
        $ex->getMessage();
        return -1;
       }
    }

    public function ValidarLogin($email, $senha)
    {
        if(empty($email) OR empty($senha)){
            return 0;
        }

        $conexao = parent::retornarConexao();

        $comando_sql = 'SELECT id_usuario, nome_usuario FROM tb_usuario WHERE email_usuario = ? AND senha_usuario = ?';

        $sql = new PDOStatement();

        $sql = $conexao->prepare($comando_sql);

        $sql->bindValue(1, $email);
        $sql->bindValue(2, $senha);

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        $sql->execute();

        $user = $sql->fetchAll();

        if(count($user) == 0){
            return -6;
        }

        $cod = $user[0]['id_usuario'];
        $nome = $user[0]['nome_usuario'];

        var_dump($cod);

        UtilDAO::CriarSessao($cod, $nome);
        header("location: pagina_inicial.php");
        exit;

    }

    public function VerificarEmailCadastro($email)
    {
        if(empty(trim($email))){
            return 0; 
        }

        $conexao = parent::retornarConexao();

        $comando_sql = 'SELECT count(email_usuario) as contar_email FROM tb_usuario WHERE email_usuario = ?';

        $sql = new PDOStatement();

        $sql = $conexao->prepare($comando_sql);

        $sql->bindValue(1, $email);

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        $sql->execute();

        $contar = $sql->fetchAll();

        return $contar[0]['contar_email'];
    }
    
    public function VerificarEmailAlteracao($email)
    {
        if(empty(trim($email))){
            return 0;
        }

        $conexao = parent::retornarConexao();

        $comando_sql = 'SELECT count(email_usuario) as contar_email FROM tb_usuario WHERE email_usuario = ? AND id_usuario != ?';

        $sql = new PDOStatement();

        $sql = $conexao->prepare($comando_sql);

        $sql->bindValue(1, $email);
        $sql->bindValue(2, UtilDAO::UsuarioLog());

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        $sql->execute();

        $contar = $sql->fetchAll();

        return $contar[0]['contar_email'];
    }

    public function CadastrarDadosUsuario($nome, $email, $senha, $rsenha)
    {

        if(empty($nome) OR empty($email) OR empty($senha) OR empty($rsenha)){
            return 0;
        }elseif(strlen($senha) < 6){
            return -2;
        }elseif($senha != $rsenha){
            return -3;
        }

        if($this->VerificarEmailCadastro($email) != 0){
            return -5;
        }

        $conexao = parent::retornarConexao();

        $comando_sql = 'INSERT INTO tb_usuario(nome_usuario, email_usuario, senha_usuario, data_cadastro) VALUES (?,?,?,?)';

        $sql = new PDOStatement();

        $sql = $conexao->prepare($comando_sql);

        $sql->bindValue(1, $nome);
        $sql->bindValue(2, $email);
        $sql->bindValue(3, $senha);
        $sql->bindValue(4, date('Y-m-d'));

        try {
            $sql->execute();
            return 1;
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return -1;
        }

    }
}

?>