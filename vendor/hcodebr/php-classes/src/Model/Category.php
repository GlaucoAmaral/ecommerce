<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


class Category extends Model{

	public static function listAll()
	{
		//listar todas categorias na pagina da administracao para termos o acesso para excluir e editar uma categoria.
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}

	public function save()//funcao para salvar no banco de dados
	{
	 	$sql = new Sql();
	 
	 	//a procedure ja insere no banco de dados e retorna a linha da insercao
	 	//como o idcategory nao vem no metodo post pois só sabemos ele apos a insercao, modificamos a classe model
	 	$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
	 		":idcategory"=>$this->getidcategory(),
	 		":descategory"=>$this->getdescategory(),
	 	));
	 	//todos os getters foram gerado DINAMICAMENTE pelos get la DO MODEL

	 	$this->setData($results[0]);//o resultado é uma linha, e apos inserir os dados no banco, insere no objeto criado. Este metodo setData é vindo da classe Model

	 	Category::updateFile();
	}


	public function get($idcategory)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));

		$this->setData($results[0]);
	}

	public function delete()//para deletar uma categoria
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		)); 

		Category::updateFile();
	}

	public static function updateFile()
	{
		//metodo para atualizar as categorias do site que ficam no footer LA EMBAIXO
		$categories = Category::listAll();//essa categories será um array com todas as linhas de categorias e as colunas
		//idcategory, descategory, dtresgister

		$html = array();

		foreach ($categories as $row) {
			//para cada linha em $categories
			//colocamos o formato html com o link do id para quando clicar ir para tal rota e tambem o nome da categoria e adicionamos num array
			array_push($html, '<li><a href="/categories/'.$row['idcategory']. '">' .$row['descategory'].'</a></li>');			
		}

		//Apos ter adicionado todas categorias no array, colocamos o conteudo do array nesse arquivo categories-menu.html transformando o array em string pela funcao implode. A funcao explode tranforma string em array.
		//Quando ocorrer uma DELECAO e um SAVE apos editar ou criar, ele atualiza o arquivo dinamicamente
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
	}

	public function getProducts($related = true)
	{
		//passamos um booleano para a funcao. se for true são os produtos relacionados a esta categoria e caso contrario os produtos nao relacionados a esta categoria
		$sql = new Sql();
		if($related === true)
		{
			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
					);", array(
						':idcategory'=>$this->getidcategory()

					));
		}
		else
		{
			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);", array(
					':idcategory'=>$this->getidcategory()
				));

		}
	}

	public function getProductsPage($page = 1, $itemsPerPage = 3)
	{
		//o primeiro paramentro passado é a pagina que estamos e o segundo parametro é a quantidade de itens que serão mostrados por pagina
		
		$start = ($page-1)*$itemsPerPage;//essa variavel start é de quanto em quanto comecarei na proxima consulta para exibir os productos

		$sql = new Sql();



		//primeiro paramatro do limit é a partir de qual registro e o segundo é quantos registros eu quero. a primeira linha sempre é o zero
		$results =  $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start, $itemsPerPage;", array(
				":idcategory"=>$this->getidcategory()
			));//resultado dos produtos

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");//resultado do TOTAL de linhas encontradas sem o uso de limit
		//sempre será 5 para sempre que formos para uma proxima pagina de aparelhos, ele printar duas paginas e tudo mais
		



		return array(
			'data'=>Product::checkList($results),//Com o checkList eu pego todas informacoes do banco e tambem seto a variavel desphoto que na pagina html pede
			'total' =>(int)$resultTotal[0]["nrtotal"],//total de produtos encontrados de acordo com o limit
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)//funcao que converte arredondando para cima. se tenho 7 items e irei dividir por 3, dará duas paginas com 3 e uma com 1
		);
		//Como o resultTotal retorna uma linha da consulta com apenas a coluna "nrtotal", entao pego a linha zero e depois a coluna "nrtotal"

	}



	public function addProduct(Product $product)
	{
		$sql = new Sql();
		$sql->query("INSERT INTO tb_productscategories VALUES(:idcategory, :idproduct)", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));
	}


	public function removeProduct(Product $product)
	{
		$sql = new Sql();
		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));

	}






}
 ?>