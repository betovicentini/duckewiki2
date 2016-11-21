<?php
class Determinacao{	
	public $usuario;
	public $data;

	public $notes; //notes
	public $detby; //detby
	public $ano; //ano
	public $mes; //mes
	public $dia; //dia
	public $conf; //conf - número de 1 a 5
	public $modif; //modif
	public $refcol; //refcol
	public $refcolnum; //refcolnum
	public $refherb; //refherb
	public $refherbnum; //refherbnum
	public $refdetby; //refdetby
	public $refano; //refano
	public $refmes; //refmes
	public $refdia; //refdia
	public $tax; //tax
	

	//construtor da classe
	function __construct($usuario, $data, $notes, $detby, $ano,$mes, $dia, $conf, $modif,$refcol, $refcolnum,$refherb, $refherbnum,$refdetby, $refano, $refmes, $refdia, $tax){			
		$this->usuario = $usuario;
		$this->data = $data;

		$this->notes = $notes;
		$this->detby = $detby;
		$this->ano = $ano;
		$this->mes = $mes;
		$this->dia = $dia;
		$this->conf = $conf;
		$this->modif = $modif;
		$this->refcol = $refcol;
		$this->refcolnum = $refcolnum;
		$this->refherb = $refherb;
		$this->refherbnum = $refherbnum;
		$this->refdetby = $refdetby;
		$this->refano = $refano;
		$this->refmes = $refmes;
		$this->refdia = $refdia;
		$this->tax = $tax;		
	}	

	public function getArray(){

		$array[0] = $this->usuario;
		$array[1] = $this->data;

		$array[2] = $this->notes;
		$array[3] = $this->detby;
		$array[4] = $this->ano;
		$array[5] = $this->mes;
		$array[6] = $this->dia;
		$array[7] = $this->conf;
		$array[8] = $this->modif;
		$array[9] = $this->refcol;
		$array[10] = $this->refcolnum;
		$array[11] = $this->refherb;
		$array[12] = $this->refherbnum;
		$array[13] = $this->refdetby;
		$array[14] = $this->refano;
		$array[15] = $this->refmes;
		$array[16] = $this->refdia;
		$array[17] = $this->tax;
		
		return $array;
	}	
} ?>
