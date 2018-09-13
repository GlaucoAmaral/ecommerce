<?php 

use \Hcode\Model\User;


//sao funcoes para serem utilzadas no template no escopo global, no namespace Global

function formatPrice(float $vlprice)
{
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