<?php
class Localidade{
	public $usuario;
	public $data;

	public $nome;	
	public $sigla;
	public $tipo;

	public $calculoLatidude;
	public $calculoLongitude;

	public $datum;
	public $utmN;
	public $utmE;
	public $utmZ;
	public $startX;
	public $startY;
	public $dimX;
	public $dimY;
	public $dimdiameter;
	public $alt1;
	public $alt2;
	
	//construtor da classe
	
	function __construct($u, $d, $nome, $sigla, $tipo, 
		$latG, $latM, $latS, $latH,
		$lonG, $lonM, $lonS, $lonH,
		$datum, $utmN, $utmE, $utmZ, $startX, $startY, $dimX, $dimY, $dimdiameter, $alt1, $alt2){		

		$this->usuario = $u;
		$this->data = $d;
		
		$this->nome = $nome;
		$this->sigla = $sigla;
		$this->tipo = $tipo;
		
		$this->calculoLatidude = abs($latG + $latM/60 + $latS/3600);
		if ($latH == 'S') {
			$this->calculoLatidude = -$this->calculoLatidude;
		}

		$this->calculoLongitude = abs($lonG + $lonM/60 + $lonS/3600);
		if ($lonH == 'W') {
			$this->calculoLongitude= -$this->calculoLongitude;
		}

		$this->datum = $datum;
		$this->utmN = $utmN;
		$this->utmE = $utmE;
		$this->utmZ = $utmZ;
		$this->startX = $startX;
		$this->startY = $startY;
		$this->dimX = $dimX;
		$this->dimY = $dimY;
		$this->dimdiameter = $dimdiameter;
		$this->alt1 = $alt1;
		$this->alt2 = $alt2;
	}	

	public function getArray(){

		$array[0] = $this->usuario;
		$array[1] = $this->data;
		$array[2] = $this->nome;
		$array[3] = $this->sigla;
		$array[4] = $this->tipo;
		$array[5] = $this->calculoLatidude;
		$array[6] = $this->calculoLongitude;
		$array[7] = $this->datum;
		$array[8] = $this->utmN;
		$array[9] = $this->utmE;
		$array[10] = $this->utmZ;
		$array[11] = $this->startX;
		$array[12] = $this->startY;
		$array[13] = $this->dimX;
		$array[14] = $this->dimY;
		$array[15] = $this->dimdiameter;
		$array[16] = $this->alt1;
		$array[17] = $this->alt2;
		
		return $array;
	}	
} ?>
