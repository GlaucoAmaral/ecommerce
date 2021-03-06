<?php 

namespace Hcode;

/*Ela classe MOdel fará todos getter e setter para todas as outras de acordo com seus respectivos atributos*/
class Model{

	private $values = [];

	public function __call($name, $args)
	{
		/*primeiro o nomedometodoinvocado e segundo o args é o valor do atributo passado  */
		//primeiro verificar se eh get ou set pois o nome vai vir tudo junto. tipo setId

		$method = substr($name, 0, 3); //traga 0,1,2, pois esta a partir da posicao 0
		$fieldName = substr($name, 3, strlen($name));//a partir da terceira até o final
		
		switch ($method)
		{
			case "get":
				return (isset($this->values[$fieldName]))?$this->values[$fieldName]:NULL;//ele retorna o campo no vetor privado values.Todos os dados já foram setado no setData
			break;
			case "set":
				$this->values[$fieldName] = $args[0];//cria-se um novo campo no vetor values com os dados. 
			break;
		}
	}

	public function setData($data = array())
	{
		foreach ($data as $key => $value) {
			$this->{"set". $key}($value);//quando criamos coisas dinamica colocamos entre as chaves. o {"set" . $key} é o nome do metodo e ($value) são os parametros
			//aqui a propria model chama os metodos dela mesmo com o $this, e após isso ela chama a funcao call passando o "set" como o $name da funcao e $args o argumento para setar

		}
	}

	public function getValues(){
		return $this->values;
	}

}


 ?>