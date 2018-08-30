<?php 


namespace Hcode; //ele esta no nameSpace Hcode


use Rain\Tpl; //Quando chamarmos um new Tpl ele sabe de onde pegar


class Page {

	private $tpl;
	private $otions = [];
	private $defaults = [
		"data" => []
	];



	public function __construct($opts = array(), $tpl_dir = "/views/")
	{
		$this->options = array_merge($this->defaults, $opts);//Funde os elementos de dois ou mais arrays de forma que os elementos de um são colocados no final do array anterior. Retorna o array resultante da fusão.

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "$tpl_dir",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
			"debug"         => false
			);
		Tpl::configure( $config );

		$this->tpl = new Tpl();

		$this->setData($this->options["data"] );

		$this->tpl->draw("header");
	}

	public function setTpl($nomeTemplate, $data = array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($nomeTemplate, $returnHTML);

	}

	private function setData($data = array())
	{//passar os dados para o template
		foreach ($data as $key => $value) {
			//o assign eu faco meio que o casamento das variaveis para o template
			$this->tpl->assign($key, $value);
		}
	}


	public function __destruct()
	{
		$this->tpl->draw("footer");
	}

}

 ?>