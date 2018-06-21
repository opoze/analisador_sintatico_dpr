<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:20:48
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-14 16:35:26
 */


namespace App\Src;

use Exception;

class SyntaxAnaliser3
{

  private $tokens = [];
  private $debug = false;
  private $failIndice = 0;

  private $lexem = '';
  private $tok = '';
  private $line = '';
  private $column = 0;
  private $indice = -1;

  function __construct($tokens = [], $debug = false){
    $this->setTokens($tokens);
    $this->debug = $debug;
  }

  // CONTROL
  public function start(){
    $this->advance();
    if($this->Program()){
      echo 'PROGRAMA ACEITO';
    }
    else{
      $tok = $this->tokens[$this->failIndice];
      echo '<br>';
      echo 'Syntax Error ';
      echo 'Unexpected token: \''. $tok['token'] . '\' ';
      echo 'in line: \''. $tok['line']. '\' column: \'' . $tok['pos'] . '\'';
    }
  }

  private function log($fn = ''){
    if($this->debug){
      echo $fn . '</br>';
    }
  }

  public function setDebug($debug = false){
    $this->debug = $debug;
  }

  private function setTokens($tokens = []){
    $this->tokens = $tokens;
  }

  private function advance() {
    $this->indice++;
    $tok = $this->getToken();
    if(is_array($tok)){
      $this->lexem = $tok['lexem'];
      $this->tok = $tok['token'];
      $this->line = $tok['line'];
      $this->column = $tok['pos'];
    }
    else{
      $this->tok = '';
    }
  }

  private function back($qty = 0) {
    for($i = 0; $i < $qty; $i++){
      $this->indice--;
      $tok = $this->getToken();
      if(is_array($tok)){
        $this->lexem = $tok['lexem'];
        $this->tok = $tok['token'];
        $this->line = $tok['line'];
        $this->column = '0';
      }
      else{
        $this->tok = '';
      }
    }
  }

  private function getToken(){
    if(isset($this->tokens[$this->indice])){
      return $this->tokens[$this->indice];
    }
    return false;
  }


  private function consume($t){
    // Cosome token ID
    if($t == 'ID') {
      if($this->lexem == 'ID' ){
        $this->advance();
      }
      else{
        return false;
      }
    }
    // Consome palavra reservada
    else if ($this->tok == $t){
      $this->advance();
    }
    // Erro
    else{
      $this->failIndice = $this->indice;
      return false;
    }

    return true;
  }


  // ANALIZER
  private function Program(){
    $this->log(__FUNCTION__);
    while($this->tok != ''){
      if(!$this->Var()){
        if(!$this->Func()){
          return false;
        }
      }
    }
    return true;
  }

  private function Var(){
    // Toda função deve iniciar logando e com  back = 0
    // Toda Vez Que consome adiociona 1 ao back
    $back = 0;
    $this->log(__FUNCTION__);

    // Se Não for Type retorna false, como não consumiu nada, não tem back

    if(!$this->Type()){
      return false;
    }

    // Se Não consumir ID retorna false, como não consumiu nada não tem back
    if(!$this->consume('ID')){
      return false;
    }

    // Consumiu então adiciona 1 a back
    $back++;

    // bloco 0 ou 1
    // Não precisa existir
    if($this->consume('[')){
      // Consumiu então adiciona 1 a back
      $back++;

      // deve existir senão volta
      if($this->lexem = 'intConstant'){
        $this->consume($this->tok);
        // Consumiu então adiciona 1 a back
        $back++;

        //deve existir senão volta
        if($this->consume(']')){
          // Consumiu então adiciona 1 a back
          $back++;
        }
        else{ //volta
          $this->back($back);
          return false;
        }
      }
      else{ //volta
        $this->back($back);
        return false;
      }
    }

    if(!$this->consume(';')){ //volta
      $this->back($back);
      return false;
    }

    return true;

  }

  private function Func(){
    // Toda função deve iniciar logando e com  back = 0
    // Toda Vez Que consome adiociona 1 ao back
    $back = 0;
    $this->log(__FUNCTION__);


    if($this->consume('def')){
      $back++;

      if($this->Type()){

        if($this->consume('ID')){
          $back++;

          if($this->consume('(')){
            $back++;

            // 0 ou 1  ParamList
            // Se der erro não tem problema
            // Significa que não tem mais parametros
            // e significa que o proximo token deve ser )
            $this->ParamList();

            if($this->consume(')')){
              $back++;

              if(!$this->Block()){
                $this->back($back);
                return false;
              }

            }
            else{
              $this->back($back);
              return false;
            }

          }
          else{
            $this->back($back);
            return false;
          }

        }
        else{
          $this->back($back);
          return false;
        }

      }
      else{
        $this->back($back);
        return false;
      }

    }
    else{
      // Não tem back
      return false;
    }

    return true;
  }

  private function ParamList(){
    $back = 0;
    $this->log(__FUNCTION__);

    if(!$this->Type()){
      return false;
    }

    if($this->consume('ID')){
      $back++;

      do {
        $end = false;

        // se não for virgula não tem problema
        // 0 ou mais
        // seta end como true
        // mas se consumir a virgula pode dar erro no restante
        // e retornar false;
        if($this->consume(',')){
          $back++;

          if($this->Type()){

            // se consumir ID entao ok é um ParamList
            // $back++;
            // lopp para o proxmi ParamList se houver
            if(!$this->consume('ID')){
              $this->back($back);
              return false;
            }

          }
          else{
            $this->back($back);
            return false;
          }

        }
        else{
          $end = true;
        }


      }
      while (!$end);


    }
    else{
      // se não consumir ID depois de Type
      // então não é ParamList
      return false;
    }

    return true;

  }

  private function Block(){
    $back = 0;
    $this->log(__FUNCTION__);

    if($this->consume('{')){
      $back++;

      // 0 ou N Var
      $isVar = true;
      while($isVar){
        $isVar = $this->Var();
      }

      // 0 ou N Var
      $isStmt = true;
      while($isStmt){
        $isStmt = $this->Stmt();
      }

      if(!$this->consume('}')){
        $this->back($back);
        return false;
      }

    }
    else{
      return false;
    }

    return true;
  }

  private function Stmt(){
    $back = 0;
    $this->log(__FUNCTION__);

    // Se Loc
    if($this->Loc()){
      if($this->consume('=')){
        $back++;
        if($this->Expr()){
          if(!$this->consume(';')){
            return false;
          }
        }
        else{
          $this->back($back);
          return false;
        }
      }
      else{
        return false;
      }
    }
    else{
      // Se FuncCall
      if($this->FuncCall()){
        if(!$this->cosume(';')){
          return false;
        }
      }
      else{
        // se if
        if($this->consume('if')){
          $back++;

          if($this->consume('(')){
            $back++;

            if($this->Expr()){

              if($this->consume(')')){
                $back++;

                if($this->Block()){

                  if($this->consume('else')){
                    $back++;
                    if(!$this->Block()){
                      $this->back($back);
                      return false;
                    }
                  }

                }
                else{
                  $this->back($back);
                  return false;
                }


              }
              else{
                // Verificar
                $this->back($back);
                return false;
              }

            }
            else{
              $this->back($back);
              return false;
            }
          }
          else{
            $this->back($back);
            return false;
          }

        }
        else{
          // se   while
          if($this->consume('while')){
            $back++;

            if($this->consume('(')){
              $back++;
              if($this->Expr()){
                if($this->consume(')')){
                  $back++;
                  if(!$this->Block()){
                    $this->back($back);
                    return false;
                  }
                }
                else{
                  $this->back($back);
                  return false;
                }
              }
              else{
                $this->back($back);
                return false;
              }

            }
            else{
              $this->back($back);
              return false;
            }

          }
          else{
            // se return
            if($this->consume('return')){
              $back++;

              $this->Expr();

              if(!$this->consume(';')){
                $this->back($back);
                return false;
              }

            }
            else{
              // se break
              if($this->consume('break')){
                $back++;
                if(!$this->consume(';')){
                  $this->back($back);
                  return false;
                }
              }
              else{
                //se continue
                if($this->consume('continue')){
                  $back++;
                  if(!$this->consume(';')){
                    $this->back($back);
                    return false;
                  }
                }
                else{
                  return false;
                }
              }
            }
          }
        }
      }
    }
    return true;
  }

  private function A(){
    $back = 0;
    $this->log(__FUNCTION__);
    // $BINOP = ['=', '+', '-', '*', '/', '%', '<', '>', '<=', '>=', '==', '!=', '&&', '||'];

    if($this->consume('=')){
      $back++;
    }
    else if($this->consume('+')){
      $back++;
    }
    else if($this->consume('-')){
      $back++;
    }
    else if($this->consume('*')){
      $back++;
    }
    else if($this->consume('/')){
      $back++;
    }
    else if($this->consume('%')){
      $back++;
    }
    else if($this->consume('<')){
      $back++;
    }
    else if($this->consume('>')){
      $back++;
    }
    else if($this->consume('<=')){
      $back++;
    }
    else if($this->consume('>=')){
      $back++;
    }
    else if($this->consume('==')){
      $back++;
    }
    else if($this->consume('!=')){
      $back++;
    }
    else if($this->consume('&&')){
      $back++;
    }
    else if($this->consume('||')){
      $back++;
    }

    if($back > 0){

      if($this->Expr()){
        if(!$this->A()){
          $this->back($back);
          return false;
        }
        else{
          return true;
        }
      }
      else{
        $this->back($back);
        return false;
      }

    }

    return true;


  }

  private function Expr(){
    $back = 0;
    $this->log(__FUNCTION__);
    // UNOP = - !


    // Se -
    if($this->consume('-')){
      $back++;
      if($this->Expr()){
        if(!$this->A()){
          $this->back($back);
          return false;
        }
      }
      else{
        $this->back($back);
        return false;
      }
    }
    else{
      // Se !
      if($this->consume('!')){
        $back++;
        if($this->Expr()){
          if(!$this->A()){
            $this->back($back);
            return false;
          }
        }
        else{
          $this->back($back);
          return false;
        }
      }
      else{
        // Se '('
        if($this->consume('(')){
          $back++;
          if($this->Expr()){
            if($this->consume(')')){
              $back++;
              if(!$this->A()){
                $this->back($back);
                return false;
              }
            }
            else{
              $this->back($back);
              return false;
            }
          }
          else{
            $this->back($back);
            return false;
          }
        }
        else{
          // Se Loc
          if($this->Loc()){
            if(!$this->A()){
              $this->back($back); // Não vai ter back
              return false;
            }
          }
          else{
            // Se FuncCall
            if($this->FuncCall()){
              if(!$this->A()){
                $this->back($back); // Não vai ter back
                return false;
              }
            }
            else{
              //se Lit
              if($this->Lit()){
                if(!$this->A()){
                  $this->back($back); // Não vai ter back
                  return false;
                }
              }
            }
          }
        }
      }
    }


    return true;

  }

  private function Type(){
    $back = 0;
    $this->log(__FUNCTION__);

    if($this->consume('int')){
        return true;
    }

    if($this->consume('bool')){
        return true;
    }

    if($this->consume('void')){
        return true;
    }

    return false;

  }

  private function Loc(){
    $back = 0;
    $this->log(__FUNCTION__);


    // Se Não consumir ID retorna false, como não consumiu nada não tem back
    if(!$this->consume('ID')){
      return false;
    }

    $back++;

    // bloco 0 ou 1
    // Não precisa existir
    if($this->consume('[')){
      $back++;

      if($this->Expr()){
        if(!$this->consume(']')){
          $this->back($back);
          return false;
        }
      }
      else{
        $this->back($back);
        return false;
      }

    }

    return true;
  }

  private function FuncCall(){
    $back = 0;
    $this->log(__FUNCTION__);

    // Se Não consumir ID retorna false, como não consumiu nada não tem back
    if(!$this->consume('ID')){
      return false;
    }

    $back++;

    // bloco 0 ou 1
    // Não precisa existir
    if($this->consume('(')){
      $back++;
      $this->ArgList();
      if(!$this->consume(')')){
        $this->back($back);
        return false;
      }
    }
    else{
      $this->back($back);
      return false;
    }

    return true;
  }

  private function ArgList(){
    $back = 0;
    $this->log(__FUNCTION__);

    if($this->Expr()){

      do {
        $end = false;
        if($this->consume(',')){
          $back++;
          if(!$this->Expr()){
            $this->back($back);
            return false;
          }
        }
        else{
          $end = true;
        }
      }
      while (!$end);


    }
    else{
      return false;
    }

    return true;


  }

  private function Lit(){
    $back = 0;
    $this->log(__FUNCTION__);


    if(
      $this->lexem == 'intConstant' ||
      $this->lexem == 'hexConstant' ||
      $this->lexem == 'stringConstant' ||
      $this->lexem == 'boolConstant' ||
      $this->lexem == 'doubleConstant'
    ){
      $this->consume($this->tok);
      return true;
    }

    return false;
  }

}
