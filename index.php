<?php 

session_start(); //inicia se a sessao

require_once("vendor/autoload.php");//Do composer. Sempre trazer as dependencias

use \Slim\Slim;//Ambos sao namesapces. Dentro do vendor tenho dezenas de classe.

$app = new \Slim\Slim();//Esse é o namespace do Slim. Isso é necessário pois ele está dentro da pasta vendor/slim/slim
//Perceba que há duas pastas slim. O namespace segue o mesmo padrão

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");
//Quando estou fazendo os require, é a mesma coisa que se eu estisse copiando todos os códigos dos outros arquivos e colando para cá

$app->run();//roda tudo

?>