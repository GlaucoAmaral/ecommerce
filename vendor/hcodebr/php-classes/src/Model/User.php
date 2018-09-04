<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{
	//A classe User é um model
	const SESSION = "User";//constante para no vetor global $_SESSION temos a posicao "User" => dadosDoUSer carregados
	const SECRET = "HcodePhp7_Secret";//chava para criptografia

	public static function login($login, $password)
	{
		$sql = new Sql(); 
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if(count($results) === 0)//nao encontrou nada(nenhuma linha), pois nada foi retornado do banco de dados
		{ 
			return false;////////////	
			throw new \Exception("Usuario inexistente ou senha inválida.");//coloco a contra barra pois a Exception está no nameSpace principal do PHP e nao dentro do namespace Hcode\Model;					
		}//pelo menos um resultado temos. Agora verificamos a senha

		$data = $results[0];

		/*PDOStatement :: fetchAll () retorna uma matriz contendo todas as linhas restantes no conjunto de resultados. A matriz representa cada linha como uma matriz de valores de coluna ou um objeto com propriedades correspondentes a cada nome de coluna. Uma matriz vazia é retornada se houver zero resultados a serem obtidos ou FALSE na falha. NO CASO COMO RETORNOU ALGO, A PRIMEIRA POSICAO DO VETOR RESULTS É UM ARRAY COM TODAS INFORMACOES DA TABELA TB_USERS QUE POSSUI AS COLUNAS:iduser, idperson, deslogin, despassword, indadmin, dtregister */

		//password_verify($password, $results["despassword"]);//recebe dois parametros. primeiro é o atributo que veio do site e o segundo do BD. Essa funcao retorna true or false se o hash bateu ou nao

		if(password_verify($password, $data["despassword"]) === true)
		{
			$user = new User(); //criamos uma propria instancia da classe, ou seja, criamos um usuário.
			/*A ideia agora é após fazer a consulta, para cada campo retornado vamos criar um atributo com o valor de cada informacao. Isso será feito atraves
			do método setData(arrayDosDados) na classe Model, que cria os atributos e os getters e setters dinamicamente.
			 */

			$user->setData($data);//chama na classe MOdel e seta os dados.Pois $data vem de uma busca no BD e retorna uma linha 

			$_SESSION[User::SESSION] = $user->getValues();//o campo "User" na variavel global $_SESSION possui todas informacoes de acordo com a busca. Todos os dados ja foram setados DINAMICAMENTE pelo fato de termos o metodo setData com o metodo magico __Call()

			return $user; //retorno o objeto Usuário criado.
			
		} else {
			throw new \Exception("Usuario inexistente ou senha inválida.");
		}


	}

	public static function verifyLogin($inadmin = true)
	{//se a pessoa nao estiver logoda, ela será redirecionada para a pagina de login
		if(
			!isset($_SESSION[User::SESSION])//se nao está setado o usuario na variavel global $_SESSION
			||
			!$_SESSION[User::SESSION]//OU NAO EXISTE A POSICAO "User" NO VETOR GLOBAL $_SESSION
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //ID NAO É MAIOR QUE ZERO, OU SEJA, negativo
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //se o admin está como usuarios??????
		){
			header("Location: /admin/login");//se cair em alguma situacao dessa, a pessoa nao está logada e ela é redirecionada para a pagina de login.
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
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");//la no index, pegamos esta lista de usuarios para listar quando clicamos em "Usuarios" na parte administrativa.
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

	 	$this->setData($results[0]);//o resultado é uma linha, e apos inserir os dados no banco, insere no objeto criado
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

	public static function getForgot($email)
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT *
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson) 
			WHERE a.desemail = :email;", 
			array(
				":email"=>$email
			)
		);
		if(count($results) === 0)//se nada foi encontrado,ou seja nao retornou nenhuma linha do banco de dados
		{
			throw new \Exception("Não foi possivel encontrar a senha.");	
		}
		else//caso foi encontrado no BD alguma linha com o email inserido vamos para recuperacao de senha
		{
			$data = $results[0];//$data esta com o valor da linha de retorno do banco de dados, e $data recebe a posicao 0(unica posicao).
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));
			

			if(count($results2) === 0)
			{//retorno da procedure.
				throw new \Exception("Não foi possível recuperar a senha.");
			}
			else
			{
				$dataRecovery = $results2[0];
				//A procedure vai retornar o idrecovery que foi a chave primaria, autoincrement... que foi gerada de um banco de dados
				//Agora vamos encriptar esse numero, vamos encriptar ele para o usuario nao conseguir ver que numero que é ou alterar e mandar como um link para o email

				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));//temos nosso codigo criptografado que será enviado com o link criptografado

				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

				//esse array passado por ultimo sao os dados para o template que é passado para o forgot.html . Temos a variavel $name e $link no forgot.html
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));
				$mailer->send();
				return $data;
			}
		}
	}
}
 ?>