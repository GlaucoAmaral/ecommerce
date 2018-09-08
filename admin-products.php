<?php


use \Hcode\PageAdmin;//estamos usando estas classes que estao em nesses diretorios
use \Hcode\Model\User;
use \Hcode\Model\Product;


$app->get("/admin/products", function(){
	User::verifyLogin();
	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", array(
		"products"=>$products
	));
});

$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");
});


$app->post("/admin/products/create", function(){
	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");

	exit;

});


$app->get("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);


	$page = new PageAdmin();

	$page->setTpl("products-update", array(
		"product"=>$product->getValues()
		//no getValues vem junto a imagem para ser carregada no edit
	));

});

$app->post("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);//pego o produto e seto no objeto $product

	$page = new PageAdmin();

	$product->setData($_POST);//seto os novos dados no produto

	$product->save();//salva todos os dados menos a foto

	//Agora o upload do arquivp/foto

	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;//para nao processar mais nada daqui para frente, senao ele fica esperando algo
});


$app->get("/admin/products/:idproduct/delete", function($idproduct){
	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /admin/products");
	exit;//para nao processar mais nada daqui para frente, senao ele fica esperando algo
});






 ?>