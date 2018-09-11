<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User; 


class Cart extends Model{
	//referente a variavel $_SESSION Esta é uma 'superglobal', ou global automática, variável. Isto simplismente significa que ela está disponível em TODOS ESCOPOS pelo script. Não há necessidade de fazer global $variable; para acessá-la dentro de uma função ou método.

	const SESSION = "Cart";//constante para no vetor global $_SESSION temos a posicao "Cart" => dadosDoUSer carregados

	public static function getFromSession()
	//essa funcao é para vermos se iniciamos um novo carrinho ou ja temos o carrinho ou pegamos da sessao... 
	{
		$cart = new Cart();//crio um novo carrinho vazio

		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0)//se a sessão ja existir, ou seja, foi definida e o id tb > 0
		{
			//significa que o meu carrinho ja foi inserido no banco e significa também que ele está na sessão. Entao vamos apenas carregar o carrinho
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);//carregando o carrinho com as informações pelo id e depois ja retorno la embaixo
		}
		else
		{
			//caso ele nao existir ainda. Vamos tentar recupar do bd por ele guardar o id da session
			$cart->getFromSessionID();//caso exista, ele ja seta no objeto Cart contem idcart, dessessionid... e ja retorna isso la no "return $cart;"
			
			if(!(int)$cart->getidcart() > 0)//se ele nao conseguiu criar o carrinho pela funcao acima, vamos criar e dps retornar o carrinho
			{

				$data = [//crio um id para a sessao
					'dessessionid'=>session_id() 
				];
				if(User::checkLogin(false) === true)//o padrao é true para uma rota administrativa. Como nao estou na administracao, estou no carrinho de compras, entao é false
					//se for verdade o checklogin quer dizer que ele está logado e consigo puxar o usuario com o getFromSession e consigo passar ao array data o id do usuario 
				{
					//caso a pessoa ja esteja logada de acordo com o User::checkLogin(false)===true
					$user = User::getFromSession();//metodo que tento carregar o objeto de acordo com a sessao 
					$data['iduser'] = $user->getiduser();//consigo obter o id user
				}

				$cart->setData($data);//colocamos os dados no carrinho, setamos. Se ele nao estiver logado nem nada, teremos apenas o idcart e dessession. Caso contrario, teremos esses dois elementos mais o iduser
				
				$cart->save();//e salvamos no banco agora

				$cart->setToSession();//como é um carrinho novo, vamos colocar na sessao
			}


		}

		return $cart;
	}

	public function setToSession()
	{
		$_SESSION[Cart::SESSION] = $this->getValues();//coloquei carrinho na sessao
	}

	public function getFromSessionID()
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", array(
			":dessessionid"=>session_id()//consigo pegar o id da sessao direto do php com essa funcao
		));

		if(count($results)>0)
		{
			$this->setData($results[0]);
		}
	}

	public function get(int $idcart)
	{
		$sql= new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", array(
			":idcart"=>$idcart
		));

		if(count($results)>0)//POde ser que seja vazio, onde nao existe o results[0]
		{
			$this->setData($results[0]);
		} 

	}

	public function save()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			":idcart"=>$this->getidcard(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays()
		]);

		$this->setData($results[0]);//aqui ja seto todas informacoes no objeto

	}

	//metodo para adicionar produto
	public function addProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", array(
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		 ));
	}

	public function removeProduct(Product $product, $all = false)
	{
		$sql = new Sql();
		if($all)
		{//aqui remove todos
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", array(
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			));
		}
		else//caso eu va remover somente uma unidade
		{
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", array(
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			));
		}
	}

	public function getProducts()
	//pegar todos produtos no carrinho
	{
		$sql = new Sql();
		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
			FROM tb_cartsproducts a
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL
			GROUP BY  b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct;", array(
				":idcart"=>$this->getidcart()
			));
		return Product::checkList($rows);
	}


}
 ?>