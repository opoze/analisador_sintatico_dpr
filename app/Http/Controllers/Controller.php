<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


use App\Src\TokenizerDecaf;
use App\Src\SyntaxAnaliser;
use App\Src\SyntaxAnaliser1;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function show(TokenizerDecaf $tokenizer) {

      $tokens = $tokenizer->load('./program.decaf');

      $syntaxAnaliser = new SyntaxAnaliser($tokens);
      $syntaxAnaliser->setDebug();
      $syntaxAnaliser->start();

    }
}
