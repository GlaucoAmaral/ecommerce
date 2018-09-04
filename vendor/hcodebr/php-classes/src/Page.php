<?php 


namespace Hcode; //ele esta no nameSpace Hcode


use Rain\Tpl; //Quando chamarmos um new Tpl ele sabe de onde pegar


class Page {

	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data" => []
	];



	public function __construct($opts = array(), $tpl_dir = "/views/")//diretorio de qual footer e header irei carregar.
	{

		//$this->defaults["data"]["session"] = $_SESSION;//

		$this->options = array_merge($this->defaults, $opts);//Funde os elementos de dois ou mais arrays de forma que os elementos de um são colocados no final do array anterior. Retorna o array resultante da fusão.



		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "$tpl_dir",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
			"debug"         => false
			);
		Tpl::configure( $config );

		$this->tpl = new Tpl();

		$this->setData($this->options["data"]);

		if ($this->options['header'] === true) $this->tpl->draw("header");//se a pagina nao precisar carregar o footer e nem o header, nao carrega.
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
		if ($this->options['footer'] === true) $this->tpl->draw("footer");
	}

}

 ?>