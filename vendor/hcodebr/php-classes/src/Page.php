<?php 


namespace Hcode; //ele esta no nameSpace Hcode


use Rain\Tpl; //Quando chamarmos um new Tpl ele sabe de onde pegar


class Page {

	private $tpl;
	private $otions = [];
	private $defaults = [
		"data" => []
	];



	public function __construct($opts = array()){
		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "/views/",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
			"debug"         => false
			);
		Tpl::configure( $config );

		$this->tpl = new Tpl();

		$this->setData($this->options["data"] );

		$this->tpl->draw("header");
	}

	public function setTpl($nomeTemplate, $data = array(), $returnHTML = false){

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