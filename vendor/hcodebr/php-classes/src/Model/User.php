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

			$user->setData($data);//chama na classe MOdel

			$_SESSION[User::SESSION] = $user->getValues();

			//var_dump($user);
			return $user;
			
		} else {
			throw new \Exception("Usuario inexistente ou senha inválida.");
		}


	}

	public static function verifyLogin($inadmin = true)
	{
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
		$_SESSION[User::SESSION] = NULL;//excluindo a session atual
	}

}



 ?>