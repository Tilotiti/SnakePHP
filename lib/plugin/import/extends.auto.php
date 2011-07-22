<?php

class import {
    static function getPhoto($url, $path, $name = false, $ext = false) {
        if(!@getimagesize($url)):
            return false;
        endif;
        
        if(!is_dir($path)):
            if(!mkdir($path, 0777, true)):
                return false;
            endif;
        endif;
        
        if(!$ext):
            $explode  = explode('/', $url);
            $fileName = $explode[count($explode)-1];
            $explode  = explode('.', $fileName);
            $ext      = $explode[count($explode)-1];
            if(preg_match('#\?#', $ext)):
                $explode = explode('?', $ext);
                $ext     = $explode[0];
            endif;
        else:
            $ext = str_replace('.', '', $ext);
        endif;
        
        if(!$name):
            $explode  = explode('/', $url);
            $name = $explode[count($explode)-1];
        endif;
        
        $explode  = explode('.', $name);
        $name     = $explode[0];
        
        if(!file_exists($path.$name.'.'.$ext) || md5_file($url) != @md5_file($path.$name.'.'.$ext)):
            if(preg_match('#^http#', $url)):
                // A télécharger
                $fp = fopen($path.$name.'.'.$ext, "w");
                fputs($fp, file_get_contents($url));
                fclose($fp);
            else:
                // A déplacer
                if(!rename($url, $path.$name.'.'.$ext)):
                    return false;
                endif;
            endif;
        endif;
        
        return $name.'.'.$ext;
    }
}

?>