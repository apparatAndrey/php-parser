<?php
    class Parser {

        public $content;
        public $pos;

        public function __construct ($content) {
            $this->content = $content;
            $this->pos = 0;
        }

        public function moveTo($string) {
            $this->pos = strpos($this->content, $string, $this->pos) + strlen($string);
        }

        public function reset() {
            $this->content = 0;
            $this->pos = 0;
        }

        public function select($from, $to) {
            $this->moveTo($from);

            // Сохраняем позицию
            $from_saved = $this->pos;

            $this->moveTo($to);
            return substr($this->content, $from_saved, $this->pos - $from_saved - strlen($to));
        }
    }