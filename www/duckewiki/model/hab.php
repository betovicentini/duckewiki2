<?php
class Habitat{
	public $usuario;
	public $data;
	public $nome;
	public $habitatPai;
	public $local;
	public $gpsId;
	public $tipo;
	public $classe;

	//construtor da classe
	function __construct($u, $d, $n, $p, $l, $gps, $tipo, $c){		
		$this->usuario = $u;
		$this->data = $d;
		
		$this->nome = $n;
		$this->habitatPai = $p;
		$this->local = $l;
		$this->gpsId = $gps;
		$this->tipo  = $tipo;

		if ($this->tipo == 'Class') {
			$this->classe = $c;
		} else {
			$this->classe = null; // ou '' ??
		}
		
	}	

	public function getArray(){

		$array[0] = $this->usuario;
		$array[1] = $this->data;

		$array[2] = $this->nome;
		$array[3] = $this->habitatPai;
		$array[4] = $this->local;
		
		$array[5] = $this->gpsId;
		$array[6] = $this->tipo;		
		$array[7] = $this->classe;
		return $array;
	}	
} 
?>
