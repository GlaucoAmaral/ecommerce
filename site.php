<?php 


use \Hcode\Page;


$app->get('/', function() {//criacao da Rota da home do site
    $page = new Page();

    $page->setTpl("index");//carrega o conteudo

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