<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

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


$app->get("/admin/categories/:idcategory/products", function($idcategory){
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);//seto os dados no objeto category

    $page = new PageAdmin();

    //Toda vez que eu clicar em alguma categoria, ele carregará o TEMPLATE padrao para a categoria mas sempre com o nome diferente, pois cada categoria eu passo um id diferente no endereco da URL
    //cada posicao do array passado é uma variavel para ser passada na pagina html
    $page->setTpl("categories-products", array(
        'category'=>$category->getValues(),
        'productsRelated'=>$category->getProducts(),
        'productsNotRelated'=>$category->getProducts(false)//false pq verdadeiro é para os produtos relacionados e false para os nao relacionados
    ));

});


$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);//seto os dados no objeto category

    $product = new product();//crio um objeto produto

    $product->get((int)$idproduct);//carrego o produto que será adicionado a tal categoria

    $category->addProduct($product);//passo para a categoria qual produto adicionarei a ela

    header("Location: /admin/categories/$idcategory/products");
    exit;
});


$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);//seto os dados no objeto category

    $product = new product();//crio um objeto produto

    $product->get((int)$idproduct);//carrego o produto que será removido de tal categoria

    $category->removeProduct($product);//passo para a categoria qual produto removerei a ela

    header("Location: /admin/categories/$idcategory/products");
    exit;
});









 ?>