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
        'errorRegister'=>User::getErrorRegister(),
        'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']//para caso a pessoa esqueca um dado na hora de preecnher os dados para o cadastro e nao perder todos os campos que ja preencheu
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
    // ['name'=>'', 'email'=>'', 'phone'=>'']
    $_SESSION['registerValues'] = NULL;//apos dár logout, os dados do Criar conta estao limpos
    header("Location: /login");
    exit;
});


$app->post("/register", function(){


    $_SESSION['registerValues'] = $_POST;//para caso a pessoa esqueca um dado na hora de preecnher os dados para o cadastro e nao perder todos os campos que ja preencheu

    if(!isset($_POST['name']) || $_POST['name'] == '')//caso a pessoa mande sem nome
    {
        User::setErrorRegister("Preencha o seu nome.");//seto o erro
        header("Location: /login");//redireciono para login
        exit;//paro a execucao do formulario
    }
    if(!isset($_POST['email']) || $_POST['email'] == '')//caso a pessoa mande sem nome
    {
        User::setErrorRegister("Preencha o seu email.");//seto o erro
        header("Location: /login");//redireciono para login
        exit;//paro a execucao do formulario
    }
    if(!isset($_POST['password']) || $_POST['password'] == '')//caso a pessoa mande sem nome
    {
        User::setErrorRegister("Preencha a senha.");//seto o erro
        header("Location: /login");//redireciono para login
        exit;//paro a execucao do formulario
    }
    if(User::checkLoginExist($_POST['email']) == true){
        User::setErrorRegister("Este endereco de email já está sendo usado por outro usuário.");//seto o erro
        header("Location: /login");//redireciono para login
        exit;//paro a execucao do formulario
    }



    $user = new User();

    $user->setData(array(
        'inadmin'=>0, //zero pois é um usuario comum
        'deslogin'=>$_POST['email'],//a forma de entrada do usuario final é com emai, ja adm é por login mesmo
        'desperson'=>$_POST['name'],
        'desemail'=>$_POST['email'],
        'despassword'=>$_POST['password'],
        'nrphone'=>$_POST['phone']
    ));

    $user->save();//aqui ocorre o save no bd

    User::login($_POST['email'], $_POST['password']);//apos ele ter feito a conta, eu ja deixo ele logado. ao inves de ter que ir para o checkout e dps o checkout mandar para login

    header("Location: /checkout");
    exit;
});

/*PARTE DE ESQUECI MINHA SENHA*/
$app->get("/forgot", function(){
    //semelhante a tela de login pois o header e o footer nao tem
   $page = new Page();
    //como para pagina de login nao carrego o header nem o footer para a pagina de login, passo essas opções para ela no vetor

    $page->setTpl("forgot");//carrega o conteudo forgot.html
});



$app->post("/forgot", function(){
    //recebo o email da pagina por POST no arrayGlobal $_POST[] no campo "email";
    $user = User::getForgot($_POST["email"], false);//pede o email para digitar. Coloco false para o lind de recuperacao nao vier com '/admin....'
    header("Location: /forgot/sent");//apos inserir o email ele redireciona para este link
    exit;

});


$app->get("/forgot/sent", function(){
    $page = new Page();
    $page->setTpl("forgot-sent");//pagina que aparece Email enviado

});


$app->get("/forgot/reset", function(){
    //Antes vamos validar a quem pertence este codigo criptografado

    $user = User::validForgotDecrypt($_GET["code"]);//na user ele retorna a linha com os dados do usuario
    //a funcao retorna iderperson, iduser,idrecovery,desip,dtrecovery,dtregister(dataenviolink),deslogin,despasword,inadmin,dtregister(dataregistrousuario),desperson,desemail,nrphone,


    $page = new Page();
    $page->setTpl("forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]//passo o code pq vou precisar validar de novo a proxima pagina apos inserir nova senha. Passo para a pagina o codigo

    ));

});


$app->post("/forgot/reset", function(){

    $forgot = User::validForgotDecrypt($_POST["code"]);//na user ele retorna a linha com os dados do usuario, caso o codigo encriptado seja valido. Recuperamos via post

    //metodos para dar um update no banco dizendo que aquela coluna. Metodo para falar no banco que essa recuperacao ja foi feita mesmo estando no prazo de uma hora
    User::setForgotUsed($forgot["idrecovery"]);


    $user = new User();
    $user->get((int)$forgot["iduser"]);//recuperando o objeto


    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

    $user->setPassword($password);#utilizamos esta nova funcao pois precisamos informar a hash da senha. Sabemos o id do usuario pelo codigo encriptado quando eh acessado pelo link de renovacao da senha. Nas consultas do banco tem a coluna iduser e com ela sabemos quem esta recuperando a senha.


    $page = new Page();
    $page->setTpl("forgot-reset-success"); //Nao passo nenhum parametro pois na pagina nao utilizamos nenhuma variavel.
});

/*FIM PARTE DE ESQUECI MINHA SENHA*/


$app->get("/profile", function(){

    User::verifyLogin(false);//verifico se a pessoa está logada. Caso contrrio, é necessario

    $user = User::getFromSession();//crio um usuario para passar para o template

    $page = new Page();

    $page->setTpl("profile",array(
        "user"=>$user->getValues(),//sempre após a pessoa fazer logim, os dados sao colocados no objeto e na session
        "profileMsg"=>User::getSucess(),
        "profileError"=>User::getError()
    ));
});

$app->post("/profile", function(){

    User::verifyLogin(false);//forco a pessoa estar logada mesmo


    //CASO POR POST ELES NAO PREENCHAM OS CAMPOS
    if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){ 
        User::setError("Preencha o seu nome.");
        header("Location: /profile");
        exit;
    }

    if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){ 
            User::setError("Preencha o seu email.");
            header("Location: /profile");
            exit;
    }

    $user = User::getFromSession();//pego o usuario da seessao

    if($_POST['desemail'] !== $user->getdesemail()){//caso a pessoa mude o endereco de email, vamos verificar se o email que ela quer usar ja nao esta cadastrado no bd 
        if(User::checkLoginExist($_POST['desemail']) === true){
            User::setError("Este endereco de email já está cadastrado.");
            header("Location: /profile");
            exit;
        }
    }

    

    $_POST['inadmin'] = $user->getinadmin();//caso a pessoa descubra e tente fazer um ataque injection por post, não irá dar certo pois o ela pega o valor que está no objeto usuario que foi retornado do banco de dados

    $_POST['despassword'] = $user->getdespassword();//mesmo caso acima. Assim será sobrescrito os valores mesmo. É como se alterasse a senha sempre pela mesma
    $_POST['deslogin'] = $user->getdeslogin();//mesmo caso acima. Assim será sobrescrito os valores mesmo. É como se alterasse a senha sempre pela mesma

    $user->setData($_POST);//seto os novos dados no objeto

    $user->save();//salvo no banco de dados
    //se ele conseguiu chegar até o save, é que as informacoes foraminseridas corretamente e podemos setar a mensagem de sucesso

    User::setSucess("Dados alterados com sucesso!");

    header("Location: /profile");
    exit;


});







 ?>