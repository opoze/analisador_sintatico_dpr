<?php

namespace App\Src;


use Exception;

class Eregs
{

    private $flag = PREG_OFFSET_CAPTURE;

    //= ( ) { } , ;

    public function lparent($str = ''){
      preg_match('/\(/', $str, $matches, $this->flag);
      return $matches;
    }

    public function rparent($str = ''){
      preg_match('/\)/', $str, $matches, $this->flag);
      return $matches;
    }

    public function lcbrace($str = ''){
      preg_match('/\{/', $str, $matches, $this->flag);
      return $matches;
    }

    public function rcbrace($str = ''){
      preg_match('/\}/', $str, $matches, $this->flag);
      return $matches;
    }

    public function coma($str = ''){
      preg_match('/\,/', $str, $matches, $this->flag);
      return $matches;
    }

    public function semicolon($str = ''){
      preg_match('/;/', $str, $matches, $this->flag);
      return $matches;
    }

    public function atrib($str = ''){
      preg_match('/=/', $str, $matches, $this->flag);
      return $matches;
    }

    public function comment($str = ''){
      preg_match('/\/\//', $str, $matches, $this->flag);
      return $matches;
    }


}
