<?php
class Especime{
	public $v1;
	public $v2;
	public $v3;
	public $v4;
	public $v5;
	public $v6;
	public $v7;
	public $v8;
	public $v9;
	public $v10;
	public $v11;
	public $v12;
	public $v13;
	public $v14;
	public $v15;
	public $v16;
	public $v17;
	public $v18;
	public $v19;
	public $txtfrmdia;
	public $selfrmmes;
	public $txtfrmano;
	
	//construtor da classe
	function __construct(){
	}

	public function retornaComoArray(){
		$arrPar = [];
		for ($i=1; $i<=19; $i++) {
			$arrPar[] = $this->{"v$i"};
		}
		return $arrPar;
	}

} ?>
