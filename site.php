<?php 


use \Hcode\Page;
use \Hcode\Model\Product;


$app->get('/', function() {//criacao da Rota da home do site
    
	$products = Product::listAll(); //pego todos produtos do bd e armazeno as informacoes na variavel $products. Mas o desphoto nao vem junto pois utilizamos o listAll que faz uma consulta no banco de dados e no bd nao tem as photos

    $page = new Page();

    $page->setTpl("index", array(
    	'products'=>Product::checkList($products)
    ));//carrega o conteudo e passo os produtos para o template.


    /*
	//echo "OK";
	//para criar a classe em um name space é: nomenossovendos\namespace\nomeclasse();
	$sql = new Hcode\DB\Sql();
	$results = $sql->select("SELECT * FROM tb_users");
	echo json_encode($results);
	//Hcode é o nosso vendor principal
	*/
});







 ?>