<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Src\Tokenizer;
use App\Src\Eregs;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function show(Tokenizer $tokenizer, Eregs $e) {

      // dd( explode('(', 'aaaaabbbbbbb'));

      // $str = 'CalculoMedia()';
      //
      // $keywords = preg_split('/[\(\)\{\},;="]+/', $str, -1, PREG_SPLIT_OFFSET_CAPTURE);
      //
      // $end = 0;
      // $toks = [];
      // foreach ($keywords as $keyword) {
      //   $start = $keyword[1];
      //   $len = strlen($keyword[0]);
      //
      //   if($start != $end){
      //     $word = substr($str, $end, $start-$end);
      //     $chars = preg_split('//', $word, -1, PREG_SPLIT_NO_EMPTY);
      //     foreach ($chars as $char) {
      //       echo $char. '<br>';
      //     }
      //   }
      //
      //   $end = $start + $len;
      //
      //   echo $keyword[0] . '<br>';
      // }

      // dd($keywords);

      return $tokenizer->load('./code.txt');





      // dd($e->comment('{}"}\,=;n\//n)'));
    }
}
