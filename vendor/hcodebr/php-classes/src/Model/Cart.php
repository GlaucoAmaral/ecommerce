<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User; 


class Cart extends Model {


	//referente a variavel $_SESSION Esta é uma 'superglobal', ou global automática, variável. Isto simplismente significa que ela está disponível em TODOS ESCOPOS pelo script. Não há necessidade de fazer global $variable; para acessá-la dentro de uma função ou método.

	const SESSION = "Cart";//constante para no vetor global $_SESSION temos a posicao "Cart" => dadosDoUSer carregados
	const SESSION_ERROR = "CartError";

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
			":idcart"=>$this->getidcart(),
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
		$this->getCalculateTotal();//funcao para atualizar o frete e total e subtotal de acordo com adicoes e remocoes no carrinho de compra
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

		$this->getCalculateTotal();
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
		return Product::checkList($rows);//colocamos checkList para setar Photo dentro de todos objetos carrinho
	}

	public function getProductsTotals()//retorna as medidas totais dos objetos, suma de valores, quantidade de objetos e tudo mais...
	{
		$sql = new Sql();
		//essa query chega no banco de dados e soma tudo de altura, preco, largura, tamanho, e tambem soma a quantidade de produtos
		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;", array(
				":idcart"=>$this->getidcart()
			));//acho que mesmo quando nao tiver nada dentro do carrinho a linha retornada contem o valor 0 para nrqtd

		if(count($results)>0)
		{
			return $results[0];
		}
		else
		{
			return [];
		}
	}


	public function setFreight($nrzipcode)
	{
		$zipcode = str_replace("-", '', $nrzipcode);//quando a pessoa insere o traco junto
		$totals = $this->getProductsTotals();

		if($totals['nrqtd']>0)
		//se existir algo dentro do carrinho
		{
			if($totals['vlheight'] < 2) $totals['vlheight']=2;//o correios nao aceita menor que 2
			if($totals['vllength'] < 16) $totals['vllength']=16;//o correios nao aceita menor que 16 
			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'37133550',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlaltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'

			]);//aqui passarei os paramentro para passar para o link de calculo ao inves de apos a virgula colocar um a um
			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs);//funcao para ler xml, o parametro passado é um camihno de um arquivo fisico ou endereco na internet
			$result = $xml->Servicos->cServico;//result esta apontando para o objeto cServico que esta denttro do codigo xml retornado e dentro de Servicos 

			if($result->MsgErro != '')//se for diferente de vazio, eh que temos erro informado pelos correios
			{
				Cart::setMsgError($result->MsgErro);
			}
			else
			{
				Cart::clearMsgError(); 	
			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);
			$this->save();
			return $result;
		}
		else
		{

		}

	}

	public static function formatValueToDecimal($value):float
	{
		$value = str_replace('.', '', $value);//troco primeiro ponto por nada
		return str_replace(',','.',$value);//substituo , por ponto  
	}


	public static function setMsgError($msg)
	{
		$_SESSION[Cart::SESSION_ERROR] = $msg; 
	}

	public static function getMsgError()
	{
		$msg =  (isset($_SESSION[Cart::SESSION_ERROR])) ? $$_SESSION[Cart::SESSION_ERROR]:"";

		Cart::clearMsgError();//limpamos a msg da sessao para nao ficar para sempre la

		return $msg; //retornamos a msg
		  
	}


	public static function clearMsgError()
	{
		$_SESSION[Cart::SESSION_ERROR] = NULL;

	}

	public function updateFreight()
	{
	//aumento do frete quando adiciono e diminuicao quando removo um item da compra
		//os valores vao alterando porque cada vez que vou adicionando no carrinho, vai somando o tamanho das caixas e tudo mais e quando faz a consulta nos correios, o preco eh maior para encomendas de acordo com tamanho, peso etc
		if($this->getdeszipcode() != 0)
		{
			$this->setFreight($this->getdeszipcode());
		}
		else
		{
		}
	}

	public function getValues()//estou sobreescrevendo esse getValues para o total da compra com o frete
	{
		$this->getCalculateTotal();

		return parent::getValues();
	}

	public function getCalculateTotal()
	{
		$this->updateFreight();//atualizo o frete
		$totals = $this->getProductsTotals();//pesquisa no bd para saber a soma das mediasd das caixas, soma do preco de tudo, soma das qts e tudo mais
		$this->setvlsubtotal($totals['vlprice']);//o campo vlprice eh a soma total dos produtos que estao no carrinho
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());//soma do valor total dos produtos mais o valor do frete 
	}

}
//todas as vezes que damos um get em algo que nao teve um set ou nao tem nada, retorna null

?>