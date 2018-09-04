<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

	const SESSION = "User";

	public static function login($login, $password)
	{
		$sql = new Sql(); 
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if(count($results) === 0)
		{ //nao encontrou nada, pois nada foi retornado do banco de dados
			throw new \Exception("Usuario inexistente ou senha inválida.");//coloco a contra barra pois a Exception está no nameSpace principal do PHP e nao dentro do namespace Hcode\Model;					
		}//pelo menos um resultado temos. Agora verificamos a senha

		$data = $results[0];

		/*PDOStatement :: fetchAll () retorna uma matriz contendo todas as linhas restantes no conjunto de resultados. A matriz representa cada linha como uma matriz de valores de coluna ou um objeto com propriedades correspondentes a cada nome de coluna. Uma matriz vazia é retornada se houver zero resultados a serem obtidos ou FALSE na falha. NO CASO COMO RETORNOU ALGO, A PRIMEIRA POSICAO DO VETOR RESULTS É UM ARRAY COM TODAS INFORMACOES DA TABELA TB_USERS QUE POSSUI AS COLUNAS:iduser, idperson, deslogin, despassword, indadmin, dtregister */

		//password_verify($password, $results["despassword"]);//recebe dois parametros. primeiro é o atributo que veio do site e o segundo do BD. Essa funcao retorna true or false se o hash bateu ou nao

		if(password_verify($password, $data["despassword"]) === true)
		{
			$user = new User(); //criamos uma propria instancia da classe
			/*A ideia agora é apos fazer a consulta, para cada campo retornado vamos criar um atributo com o valor de cada informacao */

			$user->setData($data);//chama na classe MOdel e seta os dados.Pois $data vem de uma busca no BD e retorna uma linha 

			$_SESSION[User::SESSION] = $user->getValues();//o campo "User" na variavel global $_SESSION possui todas informacoes de acordo com a busca

			return $user; //retorno o Usuario
			
		} else {
			throw new \Exception("Usuario inexistente ou senha inválida.");
		}


	}

	public static function verifyLogin($inadmin = true)
	{//se a pessoa nao estiver logoda, ela será redirecionada para a pagina de login
		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		){
			header("Location: /admin/login");
			exit;
		}
	}


	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;//excluindo a session atual. A posição user no array global será excluida, que antes carregava os dados do usuario na sessao atual.
	}


	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");//la no index, pegamos esta lista de usuarios
	}




	 public function save()//funcao para salvar no banco de dados
	 {
	 	$sql = new Sql();
	 	/*
	 	o 'p' antes é só para indicar que é um atributo da procedure, mas no bd esta sem o 'p'.
		pdesperson VARCHAR(64), 
		pdeslogin VARCHAR(64), 
		pdespassword VARCHAR(256), 
		pdesemail VARCHAR(128), 
		pnrphone BIGINT, 
		pinadmin TINYINT
	 	*/
	 	/*E agora chamamos a procedure que faz tudo. Insere nas duas tabelas os dados*/
	 	$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
	 		":desperson"=>$this->getdesperson(),
	 		":deslogin"=>$this->getdeslogin(),
	 		":despassword"=>$this->getdespassword(),
	 		":desemail"=>$this->getdesemail(),
	 		":nrphone"=>$this->getnrphone(),
	 		":inadmin"=>$this->getinadmin()
	 	));
	 	//todos os getters foram gerado DINAMICAMENTE pelos get la no model

	 	$this->setData($results[0]);//o resultado é uma linha
	 }

	 public function get($iduser)
	{
	 
	 $sql = new Sql();
	 
	 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
	 ":iduser"=>$iduser
	 ));
	 
	 $data = $results[0];//como é uma pessoa de acordo com o id, ele retorna uma linha somente. e depois seta a data no usuarios para pegarmos la no index.php
	 
	 $this->setData($data);
	 
	 }


	 public function update()
	 {
	 	$sql = new Sql();
	 	/*
	 	o 'p' antes é só para indicar que é um atributo da procedure, mas no bd esta sem o 'p'.
		pdesperson VARCHAR(64), 
		pdeslogin VARCHAR(64), 
		pdespassword VARCHAR(256), 
		pdesemail VARCHAR(128), 
		pnrphone BIGINT, 
		pinadmin TINYINT
	 	*/
	 	/*E agora chamamos a procedure que faz tudo. Insere nas duas tabelas os dados*/
	 	$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
	 		":iduser" => $this->getiduser(),
	 		":desperson"=>$this->getdesperson(),
	 		":deslogin"=>$this->getdeslogin(),
	 		":despassword"=>$this->getdespassword(),
	 		":desemail"=>$this->getdesemail(),
	 		":nrphone"=>$this->getnrphone(),
	 		":inadmin"=>$this->getinadmin()
	 	));
	 	//todos os getters foram gerado DINAMICAMENTE pelos get la no model

	 	$this->setData($results[0]);//o resultado é uma linha
	 }

	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}


}
 ?>