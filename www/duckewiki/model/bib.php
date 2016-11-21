<?php
    public class Bibliografia
    {
        private $id;
        private $addBy;
        private $addDate;
        private $bibkey;
        private $tipo;
        private $ano;
        private $autor;
        private $autores;
        private $journal;
        private $title;
        private $titlebook;
        private $pgs;
        private $vol;
        private $bibrec;

        public function __construct($id, $addBy, $addDate, $bibkey, $tipo, $ano, $autor, $autores, $journal, $title, $titlebook, $pgs, $vol, $bibrec)
        {
            $this->id = $id;
            $this->addBy = $addBy;
            $this->addDate = $addDate;
            $this->bibkey = $bibkey;
            $this->tipo = $tipo;
            $this->ano = $ano;
            $this->autor = $autor;
            $this->autores = $autores;
            $this->journal = $journal;
            $this->title = $title;
            $this->titlebook = $titlebook;
            $this->pgs = $pgs;
            $this->vol = $vol;
            $this->bibrec = $bibrec;
        }


        public Bibliografia()
        {
            $this(-1, -1, 0, "", 0, 0, "", "", "", "", "", "", "");
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

        public function setBibkey($bibkey)
        {
            $this->bibkey = $bibkey;
        }

        public function getBibkey()
        {
            return $this->bibkey;
        }

        public function setTipo($tipo)
        {
            $this->tipo = $tipo;
        }

        public function getTipo()
        {
            return $this->tipo;
        }

        public function setAno($ano)
        {
            $this->ano = $ano;
        }

        public function getAno()
        {
            return $this->ano;
        }

        public function setAutor($autor)
        {
            $this->autor = $autor;
        }

        public function getAutor()
        {
            return $this->autor;
        }

        public function setAutores($autores)
        {
            $this->autores = $autores;
        }

        public function getAutores()
        {
            return $this->autores;
        }

        public function setJournal($journal)
        {
            $this->journal = $journal;
        }

        public function getJournal()
        {
            return $this->journal;
        }

        public function setTitle($title)
        {
            $this->title= $title;
        }

        public function getTItle()
        {
            return $this->title;
        }

        public function setTitleBook($titlebook)
        {
            $this->titlebook = $titlebook;
        }

        public function getTitleBook()
        {
            return $this->titlebook;
        }

        public function setPgs($pgs)
        {
            $this->pgs = pgs;
        }

        public function getPgs()
        {
            return $this->pgs;
        }

        public function setVol($vol)
        {
            $this->vol = $vol;
        }

        public function getVol()
        {
            return $this->vol;
        }

        public function setBibrec($bibrec)
        {
            $this->bibrec;
        }

        public function getBibrec()
        {
            return $this->bibrec;
        }
    }
?>
