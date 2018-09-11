<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{
	//A classe User é um model
	const SESSION = "User";//constante para no vetor global $_SESSION temos a posicao "User" => dadosDoUSer carregados
	const SECRET = "HcodePhp7_Secret";//chava para criptografia


	public static function getFromSession()
	{
		//Essa funcao tenta recuperar um user atraves da variável session no campo User::SESSION que é igual a "User"
		//se o usuario estiver na variavel session, é possivel recuperá-lo, setá-lo e retornar este. Caso contrário retorna um usuario vazio
		$user = new User();
		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
		{
			$user->setData($_SESSION[User::SESSION]);		
		}
		//caso o objeto não conseguiu carregar, retorna um objeto vazio...
		return $user;

	}

	public static function checkLogin($inadmin = true)
	{
		if(
			!isset($_SESSION[User::SESSION])//se a sessao do usuario nao está definida 
			||
			!$_SESSION[User::SESSION]//OU está definida mas está vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //OU esta definido mas id não eh maior que zero, ou seja, nao tem o tal usuario
		)
		{
			//NAO ESTA LOGADO
			return false;
		}
		else
			//SE CAIU AQUI É PORQUE ESTÁ LOGADOO
			//primeiro ele poder administrador, depois ele pode ser usuario comum 
		{
			//se for uma verificacao na rota de administrador
			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true)
			{
				return true;
			}
			//caso ele esteja logado e nao seja administrador
			else if($inadmin === false)
			{
				return true; 
			}
			//qualquer coisa diferente disso nega tudo 
			else
			{
				return false; 
			}

		}

	}





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
		if(User::checkLogin($inadmin)){
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
	 	//todos os getters foram gerado DINAMICAMENTE pelos get la DO MODEL

	 	$this->setData($results[0]);//o resultado é uma linha, e apos inserir os dados no banco, insere no objeto criado. Este metodo setData é vindo da classe Model
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
			//data é um array de uma posicao contendo idperson, desperson, desemail, nrphone, dtregister, iduser, deslogin, despassword(criptografada), inadmin e dtregister

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));
			//insere o id e ip numa tabela e retorna esta insercao para $results2

			

			if(count($results2) === 0)
			{//retorno da procedure.
				throw new \Exception("Não foi possível recuperar a senha.");
			}
			else
			{
				$dataRecovery = $results2[0];
				//retorna a linha com IDRECOVERY, IDUSER, DESIP,DTRECOVERY, DTREGISTER
				//A procedure vai retornar o idrecovery que foi a chave primaria, autoincrement... que foi gerada de um banco de dados
				//Agora vamos encriptar esse IDRECOVERY, vamos encriptar ele para o usuario nao conseguir ver que numero que é ou alterar e mandar como um link para o email
				//temos nosso codigo criptografado que será enviado com o link criptografado
				/*INICIO ENCRIPTOGRAFIA*/
				//PEGAMOS O ID na posicao "idrecovery" que esta no results2
	            $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	            $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
	            $result = base64_encode($iv.$code);
				/*FIM ENCRIPTOGRAFIA*/

                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";
	            

				//esse array passado por ultimo sao os dados para o template que é passado para o forgot.html . Temos a variavel $name e $link no forgot.html
				//essas variaveis vao no esboco do email. $name para aparecer "Olá Glauco" e link para colocar o link encriptografado
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));
				$mailer->send();
				return $data;
			}
		}
	}







	public static function validForgotDecrypt($result)
	{
		//$result é o codigo encriptografado
		//INICIO DESCRIPTOGRAFIA
		$result = base64_decode($result);
	    $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
	    $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;
	    $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
	    //FIM DESCRIPTOGRAFIA




	    //agora com o id temos que ir no bd fazer a verificacao e ver se ele é valido ou nao com a regra de uma hora. Pois os links de redefinir senha tem uma hora de vida
		$sql = new Sql();
		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
			    AND
			    a.dtrecovery IS NULL
			    AND
			    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
			    	":idrecovery"=>$idrecovery
			    ));

		if(count($results) === 0)
		{
			throw new \Exception("Não foi possivel recuperar a senha.");
		}
		else
		{
			return $results[0];//retorno meu usuario para o index.php
		}
	}





	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();
		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE :idrecovery", array(
			":idrecovery"=>$idrecovery
		));//atualizo no banco na tabela tb_userspasswordsrecoveries na coluna dtrecovery a hora que foi feita a mudanca de senha
	}



	public function setPassword($password)
	{
		$sql = new Sql();
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password" => $password,
			":iduser"=>$this->getiduser()
		));
	}

}
 ?>