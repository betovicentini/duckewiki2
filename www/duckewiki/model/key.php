<?php
class Variaveis{
	public $usuario;
	public $data;
	public $keywords;
	public $multi;
	public $classe;
	public $def;
	public $nome;
	public $tipo;
	public $unit;
	public $alias;
	public $pathname;

	//construtor da classe
	function __construct($usuario,$data,$keywords,$multi,$classe,$def,$nome,$tipo,$unit,$alias,$pathname){
		$this->usuario = $usuario;
		$this->data = $data;
		$this->keywords = $keywords;
		$this->multi = $multi;
		$this->classe = $classe;
		$this->def = $def;
		$this->nome = $nome;
		$this->tipo = $tipo;
		$this->unit = $unit;
		$this->alias = $alias;
		$this->pathname = $pathname;
	}	
	public function getArray(){
		$array[0] = $this->usuario;
		$array[1] = $this->data;
		$array[2] = $this->keywords;
		$array[3] = $this->multi;
		$array[4] = $this->classe;
		$array[5] = $this->def;
		$array[6] = $this->nome;
		$array[7] = $this->tipo;
		$array[8] = $this->unit;
		$array[9] = $this->alias;
		$array[10] = $this->pathname;
		return $array;
	}	
} ?>
