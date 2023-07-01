<?php

    namespace App\Utils\Cache;

    class File{

        private static function getFilePath($hash){
            //DIRETORIO DE CACHE
            $dir = getenv('CACHE_DIR');

            //VERIFICA A EXISTENCIA DO DIRETORIO
            if(!file_exists($dir)) mkdir($dir, 0755, true);

            //RETORNA O CAMINHO ATE O ARQUIVO
            return $dir.'/'.$hash;
        }

        /**
         * Metodo responsavelpor guardar informacoes no cache
         * @param string $hash
         * @param mixed $content
         * @return boolean
         */
        private static function storageCache($hash, $content){
            //SERIALIZA O RETORNO
            $serialize = serialize($content);

            //OBTEM O CAMINHO ATE O ARQUIVO DE CACHE
            $cacheFile = self::getFilePath($hash);

            //GRAVA AS INFORMACOES NO ARQUIVO
            return file_put_contents($cacheFile, $serialize);
        }

        private static function getContentCache($hash, $expiration){
            //OBTEM O CAMINHO DO ARQUIVO
            $cacheFile = self::getFilePath($hash);

            if(!file_exists($cacheFile)) return false;

            //VALIDA A MODIFICACAO DO CACHE
            $createTime = filectime($cacheFile);
            $diffTime = time() - $createTime;
        
            if($diffTime > $expiration) return false;
            
            //RETORNA O DADO REAL
            $serialize = file_get_contents($cacheFile);
            return unserialize($serialize);
        }

        /**
         * Metodo responsavel por obter a cache
         * @param string $hash
         * @param integer $expiration
         * @param Closure $function
         * @return mixed
         */
        public static function getCache($hash, $expiration, $function){
            //VERIFICA O CONTEUDO GRAVADO
            if($content = self::getContentCache($hash, $expiration)) 
                return $content;
            //EXECUCAO DA FUNCAO
            $content = $function();

            //GRAVA O RETORNO NO CACHE
            self::storageCache($hash, $content);
            //RETORNA O CONTEUDO
            return $content;
        }
    }