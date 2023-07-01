<?php

    namespace App\Helper\File;

    Class Upload{

        private $name;
        private $extension;
        private $type;
        private $error;
        private $tmpName;
        private $size;
        private $duplicates = 0;
        private $path;

        public function __construct($file){
            $this->type = $file['type'];
            $this->error = $file['error'];
            $this->tmpName = $file['tmp_name'];
            $this->size = $file['size'];

            $info = pathinfo($file['name']);
            $this->name = $info['filename'];
            $this->extension = $info['extension'] ?? '';
        }

        public function setName($name){
            $this->name = $name;
        }

        public function generateNewName(){
            $this->name = time().'-'.rand(100000,999999).'-'.uniqid();
        }

        public function getBasename(){
            $extension = strlen($this->extension) ? '.'.$this->extension : '';

            $duplicates = $this->duplicates > 0 ? '-'.$this->duplicates : '';

            return $this->name.$duplicates.$extension;
        }

        public function fileSize()
        {
            $tamanho = fileSize($this->path);
            if(empty($tamanho)) return '';
            $tamanho = $tamanho/1048576;
            return number_format($tamanho,2,',','.');
        }

        private function getPossibleBasename($dir, $overwrite){
            if($overwrite) return $this->getBasename();

            $basename = $this->getBasename();

            if(!file_exists($dir.'/'.$basename)){
                return $basename;
            }

            $this->duplicates++;

            return $this->getPossibleBasename($dir, $overwrite);
        }

        public function Upload($dir, $overwrite = true){
            if($this->error != 0) return false;

            $this->path = $dir.'/'.$this->getPossibleBasename($dir, $overwrite);

            return move_uploaded_file($this->tmpName, $this->path);
        }
        public static function createMultiUpload($files){
            $uploads = [];

            foreach($files as $key => $value){
                $file = [
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];

                $uploads[] = new Upload($file);
            }

            return $uploads;
        }

    }