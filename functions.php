<?php 

use \Hcode\Model\User;


//sao funcoes para serem utilzadas no template no escopo global, no namespace Global

function formatPrice($vlprice)
{
	if(!$vlprice>0) $vlprice = 0;//trantando nao tem nada no carrinho, o valor do frete vem NULL e assim daria erro
	return number_format($vlprice, 2, ",", ".");
}

function checkLogin($inadmin = true)
{
	return User::checkLogin($inadmin);
}

function getUserName(){

	$user = User::getFromSession();
	return $user->getdesperson();

}





?>