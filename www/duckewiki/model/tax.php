<?php
class Taxon{
	public $usuario;
	public $data;
	public $nome;
	public $rank;
	public $pai;
	public $valid;
	public $especialista;
	public $autor;
	public $autortxt;
	public $ano;
	public $idadeCrown;
	public $idadeTem;
	public $valbib;
	public $bibtxt;
	public $reposid;
	public $repostxt;
	//construtor da classe
	function __construct($u, $d, $nome, $rank,$pai,$valid,$especialista,$autor,$autortxt,$ano, $idadeCrown,$idadeTem,$valbib,$bibtxt, $reposid,$repostxt){
		$this->usuario = $u;
		$this->data = $d;
		
		$this->nome = $nome;
		$this->rank = $rank;
		$this->pai = substr($pai,0,strpos($pai,'件'));
		$this->valid = $valid;
		$this->especialista = $especialista;
		$this->autor = $autor;
		$this->autortxt = $autortxt;
		$this->ano = $ano;
		$this->idadeCrown = $idadeCrown;
		$this->idadeTem = $idadeTem;
		$this->valbib = $valbib;
		$this->bibtxt = $bibtxt;
		$this->reposid = $reposid;
		$this->repostxt = $repostxt;
	}		

	public function getArray(){

		$array[0] = $this->usuario;
		$array[1] = $this->data;
		$array[2] = $this->nome;
		$array[3] = $this->rank;
		$array[4] = $this->pai;
		$array[5] = $this->valid;
		$array[6] = null;
		$array[7] = $this->especialista;
		$array[8] = 2;
		$array[9] = $this->autor;
		$array[10] = $this->autortxt;
		$array[11] = $this->ano;
		$array[12] = $this->idadeCrown;
		$array[13] = $this->idadeTem;
		$array[14] = $this->valbib;
		$array[15] = $this->bibtxt;
		$array[16] = $this->reposid;
		$array[17] = $this->repostxt;
		return $array;
	}	
} ?>
