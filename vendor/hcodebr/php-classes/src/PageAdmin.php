<?php 


namespace Hcode;


class PageAdmin extends Page{
	//tudo que for public e protegido conseguimos pegar
	public function __construct($opts = array(), $tpl_dir ="/views/admin/" ){//diretorio de qual footer e header irei carregar. No caso o header e o footer do admin
		parent::__construct($opts, $tpl_dir);//executa o construct da classe pai passando o tpl_admin como diretorio da administracao
	}
}

 ?>