<?php
class Herbario
    {
        public $addBy;
        public $addDate;
        public $sigla;
        public $nome;
        public $curad;
        public $ender;
        public $fone;
        public $url;
        public $email;
        public $contato;
        

        function __construct($addBy, $addDate, $sigla, $nome, $curad, $ender, $fone, $url, $email,$contato)
        {
            $this->addBy = $addBy;
            $this->addDate = $addDate;
            $this->sigla = $sigla;
            $this->nome = $nome;
            $this->curad = $curad;
            $this->ender = $ender;
            $this->fone = $fone;
            $this->url = $url;
            $this->email = $email;
            $this->contato = $contato;
        }
		public function getArray(){
			$array[0] = $this->addBy;
			$array[1] = $this->addDate;
			$array[2] = $this->sigla;
			$array[3] = $this->nome;
			$array[4] = $this->curad;
			$array[5] = $this->ender;
			$array[6] = $this->fone;
			$array[7] = $this->url;
			$array[8] = $this->email;
			$array[9] = $this->contato;
			return $array;
		}	
    
}
?>
