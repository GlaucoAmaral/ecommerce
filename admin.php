<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin', function() {//criacao da Rota
        
    User::verifyLogin();//se passar tudo okay, prossegue para criar a pagina de admin, caso contrario na propria funcao lá é jogado para a rota de login

    $page = new PageAdmin();

    $page->setTpl("index");//carrega o conteudo
});


$app->get('/admin/login', function() {//criacao da Rota por GET
    
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    //como para pagina de login nao carrego o header nem o footer para a pagina de login, passo essas opções para ela no vetor

    $page->setTpl("login");//carrega o conteudo
});


//$app->get('/teste', function(){
//   $page = new Teste();
//});


$app->post('/admin/login', function(){ //criacao da rota por POST
    
    User::login($_POST["login"], $_POST["password"]);//este metodo estatico da classe user recebera o login e a senha por metodo post do site

    header("Location: /admin");//após ser validado, ele é mandado para a tela de admin
    exit;
});


$app->get("/admin/logout", function(){
    User::logout();

    header("Location: /admin/login");
    exit;
});

/*PARTE DE ESQUECI MINHA SENHA*/

$app->get("/admin/forgot", function(){
	//semelhante a tela de login pois o header e o footer nao tem
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    //como para pagina de login nao carrego o header nem o footer para a pagina de login, passo essas opções para ela no vetor

    $page->setTpl("forgot");//carrega o conteudo forgot.html
});



$app->post("/admin/forgot", function(){
	//recebo o email da pagina por POST no arrayGlobal $_POST[] no campo "email";
	$user = User::getForgot($_POST["email"]);//pede o email para digitar
	header("Location: /admin/forgot/sent");//apos inserir o email ele redireciona para este link
	exit;

});


$app->get("/admin/forgot/sent", function(){
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-sent");//pagina que aparece Email enviado

});


$app->get("/admin/forgot/reset", function(){
	//Antes vamos validar a quem pertence este codigo criptografado

	$user = User::validForgotDecrypt($_GET["code"]);//na user ele retorna a linha com os dados do usuario
    //a funcao retorna iderperson, iduser,idrecovery,desip,dtrecovery,dtregister(dataenviolink),deslogin,despasword,inadmin,dtregister(dataregistrousuario),desperson,desemail,nrphone,


	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-reset", array(
    	"name"=>$user["desperson"],
    	"code"=>$_GET["code"]//passo o code pq vou precisar validar de novo a proxima pagina apos inserir nova senha. Passo para a pagina o codigo

    ));

});


$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);//na user ele retorna a linha com os dados do usuario, caso o codigo encriptado seja valido. Recuperamos via post

	//metodos para dar um update no banco dizendo que aquela coluna. Metodo para falar no banco que essa recuperacao ja foi feita mesmo estando no prazo de uma hora
	User::setForgotUsed($forgot["idrecovery"]);


	$user = new User();
	$user->get((int)$forgot["iduser"]);//recuperando o objeto


    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

	$user->setPassword($password);#utilizamos esta nova funcao pois precisamos informar a hash da senha. Sabemos o id do usuario pelo codigo encriptado quando eh acessado pelo link de renovacao da senha. Nas consultas do banco tem a coluna iduser e com ela sabemos quem esta recuperando a senha.


	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-reset-success"); //Nao passo nenhum parametro pois na pagina nao utilizamos nenhuma variavel.
});

/*FIM PARTE DE ESQUECI MINHA SENHA*/



?>