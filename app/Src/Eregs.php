<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 13:41:23
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-13 15:22:24
 */

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
