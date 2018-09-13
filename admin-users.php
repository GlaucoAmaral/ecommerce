<?php 


use \Hcode\PageAdmin;
use \Hcode\Model\User;


$app->get("/admin/users", function(){
    
    User::verifyLogin();//como inadmin está true por padrao, ele vai verficar se eh o usuario logado e que tem acesso ao administrativo

   $users = User::listAll();


    $page = new PageAdmin();
    //dentro da users.html tem um loop for que faz todo procedimento para escrever os usuarios retornados pelo listALL() e passo como segundo parametro.    
    $page->setTpl("users", array(
        "users" => $users
    ));

});

$app->get("/admin/users/create", function(){//lista todos usuarios
    User::verifyLogin();//como inadmin está true por padrao, ele vai verficar se eh o usuario logado e que tem acesso ao administrativo

    $page = new PageAdmin();
    
    $page->setTpl("users-create");

});

$app->get("/admin/users/:iduser/delete", function($iduser){//para salvarmos a edicao do usuario
    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);//$iduser vem no link

    $user->delete();

    header("Location: /admin/users");

    exit;
});//tomar muito cuidado na hora de criar as rotas na ordem




$app->get("/admin/users/:iduser", function($iduser){
    //Lá no html do users, caso a pessoa queira editar, ela chama essa rota trazendo o id do usuario do banco de dados
    User::verifyLogin();//como inadmin está true por padrao, ele vai verficar se eh o usuario logado e que tem acesso ao administrativo

    $user = new User();//crio um novo usuario

    $user->get((int)$iduser);//chamo ele de acordo com o $id


    $page = new PageAdmin();
    
    $page->setTpl("users-update", array("user"=>$user->getValues()));//passo para a pagina users-update os valores de acordo com o usuário pego, e dps acesso lá no users-update.
    //Como User é um model, logo está pegando todos os valores do usuarios. Se fosse de uma outra classe e eu tivesse dado um new em outra classe, pegaria os valores da outra classe


});
//se acessar via get, ele responde com html. se for via post ele faz a insercao dos dados



$app->post("/admin/users/create", function(){//parte de insert do usuario

    User::verifyLogin();


    //var_dump($_POST);

    $user = new USer();

    //No user-create ele esta assumindo valor de "1" somente se a caixa for selecionada.Caso contraria nada. Entao para nao dar erro, colocamos esse if ternario. Se ele for definidor, é 1, caso contrario é zero
    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    // $_POST['despassword'] = User::getPasswordHash($_POST['despassword']);
    $_POST['despassword'] = $_POST['despassword'];//passando a senhha sem estar criptografada. O metodo save fará isso

    $user->setData($_POST);
    //No html utilizamos no nome dos campos do html os mesmos nomes da tabela no banco de dados. Entao ele vai criar um atributo para cada um desses valores que a gente tem. Logo comseguimos fazer getdesperson;

    $user->save();//funcao do save é executar o insert dentro do banco, juntamente com os gets criados dinamicamente na funcao save().

    //var_dump($user);

    header("Location: /admin/users");
    exit;
});


$app->post("/admin/users/:iduser", function($iduser){//para salvarmos a edicao do usuario
    //quando o usuario der Salvar no editar
    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->get((int)$iduser);//carrega os valores no $user(objeto)

    $user->setData($_POST);//por post vem todos os dados novos alterados

    $user->update();

    header("Location: /admin/users");
    exit;

});














 ?>