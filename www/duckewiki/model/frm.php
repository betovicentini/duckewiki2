<?php
    public Formulario
    {
        private $id;
        private $addBy;
        private $addDate;
        private $nome;
        private $fields;
        private $shared;
        private $hab;

        public function __construct($id, $addBy, $addDate, $nome, $fields, $shared, $hab)
        {
            $this->id = $id;
            $this->addBy = $addBy;
            $this->addDate = $addDate;
            $this->$nome = $nome;
            $this->fields = $fields;
            $this->shared = $shared;
            $this->hab = $hab;
        }

        public Formulario()
        {
            $this(-1, -1, 0, "", "", "", "");
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

        public function setFields($fields)
        {
            $this->fields = $fields;
        }

        public function getFields()
        {
            return $this->fields;
        }

        public function setShared($shared)
        {
            $this->shared = $shared;
        }

        public function getShared()
        {
            return $this->shared;
        }

        public function setHab($hab)
        {
            $this->hab = $hab;
        }

        public function getHab()
        {
            return $this->hab;
        }
    }
?>
