<?php 


use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;



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


$app->get("/categories/:idcategory", function($idcategory){
    //aqui esta a parte de mexermos na categoria em relacao ao visual para os clientes do ecommerce

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//numero da pagina

    $category = new Category();

    $category->get((int)$idcategory);//seto os dados no objeto category

    $pagination = $category->getProductsPage($page);//a funcao getProductsPage() retorna um array com 3 campos: 'data'(todosprodutos com desphoto, 'total'(quantidade de produtos), 'pages'(total de paginas).

    $pages = [];


    for ($i=1; $i <= $pagination['pages']; $i++){ 
        array_push($pages, [
            'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
            'page'=>$i
        ]);
    }

    $page = new Page();

    //Toda vez que eu clicar em alguma categoria, ele carregará o TEMPLATE padrao para a categoria mas sempre com o nome diferente, pois cada categoria eu passo um id diferente no endereco da URL
    //cada posicao do array passado é uma variavel para ser passada na pagina html
    $page->setTpl("category", array(
        'category'=>$category->getValues(),
        'products'=>$pagination["data"],
        'pages'=>$pages
    ));
});



$app->get("/products/:desurl", function($desurl){
    //esse desurl ja vem direto após a gente clicar no produto, pois na view category.html puxamos os dados dos productos de acordo com a categoria
    $product = new Product();

    $product->getFromURL($desurl);//nessa funcao ja capturamos do banco de dados as informacoes do produto e no final ja setamos ele atravas do $this->setData($results[0]);

    $page = new Page();

    $page->setTpl("product-detail", [
        'product' => $product->getValues(),//aqui passamos as informações do produto
        'categories'=>$product->getCategories()//aqui passamos as quais categorias o produto esta relacionado
    ]);
});



$app->get("/cart", function(){
    
    $cart = Cart::getFromSession();

    $page = new Page();

    $page->setTpl("cart", [
    ]);
});



 ?>