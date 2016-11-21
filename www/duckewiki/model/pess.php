<?php
class Pessoa{
	public $usuario;
	public $data;
	public $nome;	
	public $sobreNome;
	public $ultimoSobreNome;
	public $abreviacao;
	public $email;
	public $notas;

	//construtor da classe
	function __construct($u, $d, $n, $sn, $us, $e, $no, $a){
		$this->usuario = $u;
		$this->data = $d;
		
		$this->nome = $this->corrigeNome($n);
		$this->sobreNome = $this->corrigeNome($sn);
		$this->ultimoSobreNome = $this->corrigeNome($us);

		$this->abreviacao = $a;
		$this->email = $e;
		$this->notas = $no;
	}	

	private function corrigeNome($nome) {
		if (ctype_upper($nome[0]) && ctype_upper($nome[1])) {
			return $nome[0].strtolower(substr($nome,1));
		} else {
			return $nome;
		}
	}

	public function getArray(){

		$array[0] = $this->usuario;
		$array[1] = $this->data;

		$array[2] = $this->nome;
		$array[3] = $this->sobreNome;
		$array[4] = $this->ultimoSobreNome;
		
		$array[5] = $this->email;
		$array[6] = $this->notas;		
		$array[7] = $this->abreviacao;
		return $array;
	}	
} ?>
