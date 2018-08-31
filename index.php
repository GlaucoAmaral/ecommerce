<?php 

require_once("vendor/autoload.php");//Do composer. Sempre trazer as dependencias

use \Slim\Slim;//Ambos sao namesapces. Dentro do vendor tenho dezenas de classe.
use \Hcode\Page;//Pegar as que estao nestes namespace. Carrega somente do Slim e do Hcode
use \Hcode\PageAdmin;

$app = new \Slim\Slim();//

$app->config('debug', true);

$app->get('/', function() {//criacao da Rota
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

$app->get('/admin', function() {//criacao da Rota
    $page = new PageAdmin();

    $page->setTpl("index");//carrega o conteudo
});


$app->get('/admin/login', function() {//criacao da Rota
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    //como para pagina de login nao carrego o header nem o footer para a pagina de login, passo essas opções para ela no vetor

    $page->setTpl("login");//carrega o conteudo


});





$app->run();//roda tudo

 ?>