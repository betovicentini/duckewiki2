<?php
class Categoria{
	public $usuario;
	public $data;
	public $valdef; //definicao da variavel
	public $valname; //nome da variavel

	//construtor da classe
	function __construct($usuario,$data,$valdef,$valname){
		$this->usuario = $usuario;
		$this->data = $data;
		$this->valdef = $valdef;
		$this->valname = $valname;
	}	
	public function getArray(){
		$array[0] = $this->usuario;
		$array[1] = $this->data;
		$array[3] = $this->valdef;
		$array[4] = $this->valname;
		return $array;
	}	
} ?>
