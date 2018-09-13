<?php 


use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


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
        'cart'=>$cart->getValues(),
        'products'=>$cart->getProducts(),
        'error'=>Cart::getMsgError()
    ]);
});


$app->get("/cart/:idproduct/add", function($idproduct){ 
    $product = new Product();//crio uma instancia do novo produto

    $product->get((int) $idproduct);//seto o produto de acordo com o id dele

    $cart = Cart::getFromSession();//pego o carrinho pela sessao ou crio um novo

    $qtd = (isset($_GET['qtd']))?(int)$_GET['qtd']:1;//se vier 4 itens de uma vez é quatro, caso contrario um somente por padrao

    for($i=0; $i<$qtd; $i++)
    {
        $cart->addProduct($product);
    }

    header("Location: /cart");
    exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){//somente quando quero remover um
    $product = new Product();//crio uma instancia do novo produto

    $product->get((int) $idproduct);//seto o produto de acordo com o id dele

    $cart = Cart::getFromSession();//pego o carrinho pela sessao ou crio um novo


    $cart->removeProduct($product);

    header("Location: /cart");
    exit;

});


$app->get("/cart/:idproduct/remove", function($idproduct){//Deletar todos
    $product = new Product();//crio uma instancia do novo produto

    $product->get((int) $idproduct);//seto o produto de acordo com o id dele

    $cart = Cart::getFromSession();//pego o carrinho pela sessao ou crio um novo

    $cart->removeProduct($product, true);

    header("Location: /cart");
    exit;

});


$app->post("/cart/freight",function(){


    $cart = Cart::getFromSession();//pego o carrinho da sessao

    $cart->setFreight($_POST['zipcode']);//passo o cpf para calcular o frete que vem de acordo com o html

    header("Location: /cart");
    exit;
});

//apos a pessoa clicar no botao de finalizar compra
$app->get("/checkout", function(){

    User::verifyLogin(false);//verificar se o usuario esta logado. Como nao é uma rota para o login da administracao, eu passo false para o parametro $inadmin, assim redirecionarei para uma rota de login do tipo usuerio nao administrador. E caso ele nao esteja logado, eh redirecionado para a pagina de login de usuario comum

    $cart = Cart::getFromSession();

    $address = new Address();

    $page = new Page();

    $page->setTpl("checkout", array(
        'cart'=>$cart->getValues(),
        'address'=>$address->getValues()
    ));
});

$app->get("/login", function(){
    //é logico que nao faco a verificao de login pois é aqui mesmo que quero que ele faca login
    $page = new Page();

    $page->setTpl("login", array(
        'error'=>User::getError(),//apos o erro ser pegado ele ja eh limpado da variavel super globar $_session
    ));

}); 

$app->post("/login", function(){

    try {
        //nesse post eu recebo os dados do login e password para fazer o login
         User::login($_POST['login'], $_POST['password']);
         //se ocorrer erro ao tentar fazer o login, o erro sai da classe de la e vem para cá, ai eu pego o erro daqui e seto com a funao static da User e depois disso o erro estará na variavel $_SESSION 
    } catch (Exception $e) {

        User::setError($e->getMessage());
        
    }
    

    header("Location: /checkout");
    exit;

});


$app->get("/logout", function(){
    User::logout();
    header("Location: /login");
    exit;
});







 ?>