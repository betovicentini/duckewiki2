<?php
    public class Projeto
    {
        private $id;
        private $addBy;
        private $addDate;
        private $nome;
        private $url;
        private $financ;
        private $procs;
        private $logo;
        private $equipe;
        private $morffrm;
        private $habfrm;

        public function __construct($id, $addBy, $addDate, $nome, $url, $financ, $procs, $logo, $equipe, $morffrm, $habfrm)
        {
            $this->id = $id;
            $this->addBy = $addBy;
            $this->addDate = $addDate;
            $this->nome = $nome;
            $this->url = $url;
            $this->financ = $financ;
            $this->procs = $procs;
            $this->logo = $logo;
            $this->equipe = $equipe;
            $this->morffrm = $morffrm;
            $this->habfrm = $habfrm;
        }

        public Projeto()
        {
            $this(-1, -1, 0, "", "", "", "", "", "", -1, -1);
        }

        public function setId($id)
        {
            $this->id = $id;
        }

        public function getId()
        {
            return $this->id;
        }

        public function setAddBy($addBy)
        {
            $this->addBy = $addBy;
        }

        public function getAddBy()
        {
            return $this->addBy;
        }

        public function setAddDate($addDate)
        {
            $this->addDate = $addDate;
        }

        public function getAddDate()
        {
            return $this->addDate;
        }

        public function setNome($nome)
        {
            $this->nome = $nome;
        }

        public function getNome()
        {
            return $this->nome;
        }

        public function setUrl($url)
        {
            $this->url = $url;
        }

        public function getUrl()
        {
            return $this->url;
        }

        public function setFinanc($financ)
        {
            $this->financ = $financ;
        }

        public function getFinanc()
        {
            return $this->financ;
        }

        public function setProcs($procs)
        {
            $this->procs = $procs;
        }

        public function getProcs()
        {
            return $this->procs;
        }

        public function setLogo($logo)
        {
            $this->logo = $logo;
        }

        public function getLogo()
        {
            return $this->logo;
        }

        public function setEquipe($equipe)
        {
            $this->equipe = $equipe;
        }

        public function getEquipe()
        {
            return $equipe;
        }

        public function setMorffrm($morffrm)
        {
            $this->morffrm = $morffrm;
        }

        public function getMorffrm()
        {
            return $this->morffrm;
        }

        public function setHabfrm($habfrm)
        {
            $this->habfrm = $habfrm;
        }

        public function getHabfrm()
        {
            return $this->habfrm;
        }
    }
?>
