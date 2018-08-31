<?php


namespace Hcode;


class Teste{

	private $teste = array();

	public function __Construct(){
	$this->teste["data"] = array(
		"session" => date("d/m/Y H:i:s"));
	var_dump($this->teste);
	echo '<br>';
	$this->teste["data"]["session"] = date("d/m/Y H:i:s");
	//$this->teste = "Glauco";//SEMPRE QUANDO VAMOS NOS REFERENCIAR ÁS VARIAVEIS E METODOS, NAO COLOCAMOS O $DE DOLAR
	var_dump($this->teste);


}



}



	




 ?>