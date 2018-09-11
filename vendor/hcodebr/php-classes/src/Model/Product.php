<?php
//se der erro por causa do namespace, pode ser algo relacionado ao espaco

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model {

	public static function listAll()
	{
		//listar todas categorias na pagina da administracao para termos o acesso para excluir e editar uma categoria.
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}



	public static function checkList($list)
	{
		foreach ($list as &$row) {
			$p = new Product();//crio um novo produto
			$p->setData($row);//seto o produto de acordo com cada linha retornada do bd 
			$row = $p->getValues();//agora a linha esta recebendo todos o dados do BD juntamente com a foto			
		}
		return $list;//retono o array formatado com AS FOTOS
	}


	public function save()//funcao para salvar no banco de dados
	{
	 	$sql = new Sql();
	 	//a procedure ja insere no banco de dados e retorna a linha da insercao
	 	//a tabela tb_product possui as seguinte colunas:
	 	/*
		idproduct
		desproduct
		vlprice
		vlwidth
		vlheight
		vllength
		vlweight
		desurl
		dtregister(preenche automaticamente)
	 	*/
	 	$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
	 		":idproduct"=>$this->getidproduct(),
	 		":desproduct"=>$this->getdesproduct(),
	 		":vlprice"=>$this->getvlprice(),
	 		":vlwidth"=>$this->getvlwidth(),
	 		":vlheight"=>$this->getvlheight(),
	 		":vllength"=>$this->getvllength(),
	 		":vlweight"=>$this->getvlweight(),
	 		":desurl"=>$this->getdesurl()
	 	));
	 	//todos os getters foram gerado DINAMICAMENTE pelos get la DO MODEL

	 	var_dump($results);
	 	$this->setData($results[0]);//o resultado é uma linha, e apos inserir os dados no banco, insere no objeto criado. Este metodo setData é vindo da classe Model
	}


	public function get($idproduct)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$idproduct
		));

		$this->setData($results[0]);
	}

	public function delete()//para deletar uma categoria
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));
	}

	public function checkPhoto()
	{
		//se a foto existir e o nome da foto é o id
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct(). ".jpg"))
		{
			//usamos barra pois aqui é url e em cima é diretorio no sistema operacional
			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
		}
		else
		{
			$url = "/res/site/img/product.jpg";//imagem cinza
		}

		return $this->setdesphoto($url);//estamos colocando dentro do objeto esta url e retornando. La na pagina products-update.html essa variavel eh passada no src=""...
	}


	//reescrevendo o metodo getValues e retonando os valores para colocarmos na pagina de editar produto
	public function getValues()
	{
		$this->checkPhoto();//esse metodo seta a photo no objeto para na hora de editarmos nao termo problema com o campo da imagem
		$values = parent::getValues();//puxa os dados do pai, pois nesse getValues é retornado o array Values que foi setado com todos campos do BD atraves da funcao setData

		return $values;
	}

	//Funcao para setar a photo na parte de "editar" produto
	public function setPhoto($file)
	{
		//estamos sempre eperando arquivo jpg, mas a pessoa pode enviar outro tipo. Logo, vamos fazer uma conversao
		$extension = explode(".", $file["name"]);//explodiu a string em dois array onde tinha o ponto da extensao no nome do arquivo
		//Quando passamos uma foto por referencia, ela vem em formato de array com os campos: file(conteudo da imagem), name(nomeinteirodaimgcomextensao), type(), tmp_name(localizaocao no lugar temporario), size(tamanho da fto);
		$extension = end($extension);//pego a extensao do arquivo no array

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
			$image = imagecreatefromjpeg($file["tmp_name"]);//endereco com nome temporario do arquvo no servidor. //C:\xamp\tmp\php9D68.tmp
			//retorna um identificador de imagem representando a imagem obtida através do nome de arquivo dado.
			break;

			case "gif":
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

			case "png":
			$image = imagecreatefrompng($file["tmp_name"]);
			break;
		}
		//Uma vez que jogamos dentro do $image(da biblioteca GD do php) é uma imagem e agora podemos jogar(transformar) para o que quisermos.
		//apos isso ele ja pegou a imagem, nao interessando o formato do arquivo
		//(variavel, destino)
		$destino = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct(). ".jpg";
		
		imagejpeg($image, $destino);
		
		imagedestroy($image);//destruimos o ponteiro da imagem

		$this->checkPhoto();

	}

	public function getFromURL($desurl)
	{
		$sql = new SQl();

		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1;", array(
			':desurl'=>$desurl
		));
		//limit 1 para garantir que retorna uma linha somente


		$this->setData($rows[0]);//setamos o objeto
	}

	public function getCategories()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = 5;", array(
			":idproduct"=>$this->getidproduct()
		));
	}






}
?>