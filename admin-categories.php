<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get("/admin/categories", function(){
    User::verifyLogin();
    
    $categories = Category::listAll();

    $page = new PageAdmin();

    $page->setTpl("categories", [
        "categories"=>$categories
    ]);//passo todas as categorias para o site categories.html com do lado a funcao e "editar" e "excluir" as categorias.
});

$app->get("/admin/categories/create", function(){
    User::verifyLogin();
    
    $page = new PageAdmin();

    $page->setTpl("categories-create");
});


$app->post("/admin/categories/create", function(){
    User::verifyLogin();


    $category = new Category();

    $category->setData($_POST);//setar a nossa variavel post, pegar os mesmos names no array $_POST e colocar no objeto

    $category->save();//salvo no bd

    header("Location: /admin/categories");//redirecioono
    exit;

});


$app->get("/admin/categories/:idcategory/delete", function($idcategory){

    User::verifyLogin();


    $category = new Category();

    $category->get((int)$idcategory);

    $category->delete();

    header("Location: /admin/categories");//redirecioono
    exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){
    //rota utilizada na edicao da categoria em relacao a pagina html
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update", array(
        "category" => $category->getValues()
    ));
});

$app->post("/admin/categories/:idcategory", function($idcategory){
    //rota utiliada para edicao do nome da categoria no envio do dado.
    
    User::verifyLogin();
    $category = new Category();

    $category->get((int)$idcategory);

    $category->setData($_POST);
    //var_dump($_POST);//array(1) { ["descategory"]=> string(7) "Android" }

    $category->save();

    header("Location: /admin/categories");
    exit;
});


$app->get("/categories/:idcategory", function($idcategory){
    //aqui esta a parte de mexermos na categoria em relacao ao visual para os clientes do ecommerce

    $category = new Category();

    $category->get((int)$idcategory);//seto os dados no objeto category

    $page = new Page();

    //Toda vez que eu clicar em alguma categoria, ele carregará o TEMPLATE padrao para a categoria mas sempre com o nome diferente, pois cada categoria eu passo um id diferente no endereco da URL
    //cada posicao do array passado é uma variavel para ser passada na pagina html
    $page->setTpl("category", array(
        'category'=>$category->getValues(),
        'producrs'=>[]
    ));
});




 ?>