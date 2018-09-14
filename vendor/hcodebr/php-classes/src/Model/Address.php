<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;


class Address extends Model {


	const SESSION_ERROR = "AdressError";
	public static function getCep($nrcep)
	{

		$nrcep = str_replace("-", "", $nrcep);//retirando o - por nada. Assim nosso cep tem apenas os numero

		$ch = curl_init(); //informa ao php que vamos iniciar o rastreio de um Url

		curl_setopt($ch, CURLOPT_URL,"https://viacep.com.br/ws/$nrcep/json/");//nossavariavelresource, opcaopelaqualfazerachamda,url

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//resource, parasaberseeletemquedevolverparanos, treupoiseleesperaretorno

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//se iremos exigir alguma verificacao ssl

		$data = json_decode(curl_exec($ch), true);//passo true para vir como ponteiro e nao objeto

		curl_close($ch);//sempre fechar, pois caso contrario, sempre que der um f5, irá abrir mais uma referencia de memoria

		return $data;
	}

	public  function loadFromCEP($nrcep)
	{
		// echo "tipo quando vem vazio";
		// var_dump($this->getdescomplemento());//string(0) ""
		// exit;


		$data = Address::getCEP($nrcep);//carrego as informacoes

		if(isset($data['logradouro']) && $data['logradouro'])//se o logradouro existir e ele nao vou vazio
		{
			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');//colocamos estatico pois nao vem com a consulta
			$this->setdeszipcode($nrcep);
		}


	}


	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_addresses_save (:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", array(
			":idaddress"=>$this->getidaddress(),
			":idperson"=>$this->getidperson(),
			":desaddress"=>utf8_decode($this->getdesaddress()),
			":descomplement"=>utf8_decode($this->getdescomplement()),
			":descity"=>utf8_decode($this->getdescity()),
			":desstate"=>utf8_decode($this->getdesstate()),
			":descountry"=>utf8_decode($this->getdescountry()),
			":deszipcode"=>$this->getdeszipcode(),
			":desdistrict"=>$this->getdesdistrict()
		));

		if(count($results)>0)
		{
			$this->setData($results[0]);//seto tudo no objeto
		}
	}


	public static function setMsgError($msg)
	{
		$_SESSION[Address::SESSION_ERROR] = $msg; 
	}

	public static function getMsgError()
	{
		$msg =  (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR]:"";
		Address::clearMsgError();//limpamos a msg da sessao para nao ficar para sempre la
		return $msg; //retornamos a msg
		  
	}


	public static function clearMsgError()
	{
		$_SESSION[Address::SESSION_ERROR] = NULL;

	}





}
?>