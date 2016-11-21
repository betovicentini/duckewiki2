<?php
class Amostra{
	public $usr;
	public $data;
	public $tag;
	public $tipo;
	public $extdate;
	public $col;
	public $esp;
	public $pl;
	public $loc;
	//construtor da classe
	function Habitat($u,$d,$tag,$tipo,$ed,$col,$esp,$pl,$loc){		
		$this->usr = $u;
		$this->data = $d;
		$this->tag = $tag;
		$this->tipo = $tipo;
		$this->extdate = $ed;
		$this->col = $col;
		$this->esp  = $esp;
		$this->pl = $pl;
		$this->loc = $loc;
	}	
	public function getArray(){
		$array[0] = $this->usr;
		$array[1] = $this->data;
		$array[2] = $this->tag;
		$array[3] = $this->tipo;
		$array[4] = $this->extdate;
		$array[5] = $this->col;
		$array[6] = $this->esp;		
		$array[7] = $this->pl;
		$array[8] = $this->loc;
		return $array;
	}	
} 
?>
