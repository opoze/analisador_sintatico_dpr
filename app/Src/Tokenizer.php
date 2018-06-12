<?php

namespace App\Src;


use Exception;
use App\Src\Eregs;

class Tokenizer
{

    private $tokens = [];
    private $eregs = null;
    private $reservedWords = [];
    private $ids = [];
    private $currentId = [];
    private $context = '';
    private $lexemas = [];

    function __construct(){
      $this->eregs = new Eregs();
      $this->reservedWords = [
        '#include',
        'void',
        'float',
        'printf',
        'int',
        'string',
        'if',
        'NULL',
        'for',
        'return',
      ];
    }


    public function load($file = '') {
      try{
        $handle = fopen($file, "r");
      }
      catch(Exception $e){
        return $e->getMessage();
      }
      if ($handle) {
          while (($line = fgets($handle)) !== false) {
              $this->proccessLine($line);
          }
          fclose($handle);
          $this->lexemas();
      } else {
          return 'Error loading file';
      }
    }

    private function proccessLine($line = ''){
      $line = trim($line);
      if(strlen($line) > 0 ){
        $str = $this->removeLineComment($line);
        $words = explode(' ', $str);
        foreach ($words as $word) {
          $this->split($word);
        }
        $this->tokens[] = '##';
      }
    }

    private function split($str = ''){
      $keywords = preg_split('/[\s\(\)\{\},;=<>!&\[\]+"]+/', $str, -1, PREG_SPLIT_OFFSET_CAPTURE);
      $end = 0;
      $toks = [];
      foreach ($keywords as $keyword) {
        $start = $keyword[1];
        $len = strlen($keyword[0]);
        if($start != $end){
          $word = substr($str, $end, $start-$end);
          $len2 = strlen($word);
          if($len2 > 0){
            $keywords1 = preg_split('//', $word, -1, PREG_SPLIT_OFFSET_CAPTURE);
            foreach ($keywords1 as $keyword1) {
              $len1 = strlen($keyword1[0]);
              if($len1 > 0 ){
                // echo htmlspecialchars($keyword1[0]) . '<br>';
                $this->tokens[] = $keyword1[0];
              }
            }
          }
        }

        $end = $start + $len;
        if($len > 0){
          // echo htmlspecialchars($keyword[0]) . '<br>';
          $this->tokens[] = $keyword[0];
        }
      }
    }

    private function removeLineComment($str = '') {
      $matches = $this->eregs->comment($str);
      if(!empty($matches)){
        $pos = $matches[0][1];
        return substr($str, 0, $pos);
      }
      return $str;
    }

    private function setContext($str = ''){
      $this->context = $str;
    }

    private function lexemas(){

      $context = 0;
      $next1 = false;
      $next2 = false;
      $string = '';
      $last1 = false;
      $last2 = false;
      $pula = false;

      // dd($this->tokens);

      foreach ($this->tokens as $key => $token) {

        // Somente para impressao na tela
        if($token=='##'){
          echo '<br>';
          continue;
        }

        // Quando lido tokens duplos ex ++
        if($pula){
          $pula=false;
          continue;
        }

        // Strings esto em mais de um token
        if($this->context == 'string'){
          if($token=='"'){
            echo $string . ' ]  ';
            // echo '<br>';
            $string = '';
            $this->setContext('');
          }
          else{
            $string.= ' '.$token;
          }
          continue;
        }

        // Comentarios de mais de uma linha
        if($this->context == 'comment'){
          if($token=='*/'){
            $this->setContext('');
          }
          continue;
        }

        // Proximos dois tokens
        if(isset($this->tokens[$key+1])){
          $next1 = $this->tokens[$key+1];
        }

        if(isset($this->tokens[$key+2])){
          $next2 = $this->tokens[$key+2];
        }

        // Dois tokens anteriores
        if(isset($this->tokens[$key-1])){
          $last1 = $this->tokens[$key-1];
        }

        if(isset($this->tokens[$key-2])){
          $last2 = $this->tokens[$key-2];
        }

        // Detecta tipos de tokens
        if(in_array($token, $this->reservedWords)){
          echo '[ reserved_word, ' . $token . ' ]  ';
          // echo '<br>';
          $this->lexemas[$token] = 'reserved_word';
        }

        else if($token == ','){
           echo '[ comma, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if($token == ';'){
           echo '[ semicolon, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if($token == '{'){
           echo '[ l_cbrace, ' . $token . ' ]  ';
           // echo '<br>';
           $context++;
        }

        else if($token == '}'){
           echo '[ r_cbrace, ' . $token . ' ]  ';
           // echo '<br>';
           $context--;
        }

        else if($token == '('){
           echo '[ l_parent, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if($token == ')'){
           echo '[ r_parent, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if($token == '<' && $next2 == '>'){
           echo '[ include, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if($token == '>' && $last2 == '<'){
           echo '[ include, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if($last1 == '<' && $next1 == '>'){
           echo '[ include, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if ($token == '=' && $next1 == '='){
           echo '[ Relat_op, ' . '==' . ' ]  ';
           // echo '<br>';
           $pula = true;
        }

        else if ($token == '='){
           echo '[ Equal_op, ' . '=' . ' ]  ';
           // echo '<br>';
        }

        else if ($token == '+' && $next1 == '+'){
           echo '[ Inc_op, ' . '++' . ' ]  ';
           // echo '<br>';
           $pula = true;
        }

        else if ($token == '+'){
           echo '[ Arit_op, ' . '+' . ' ]  ';
           // echo '<br>';
        }

        else if ($token == '-'){
           echo '[ Arit_op, ' . '-' . ' ]  ';
           // echo '<br>';
        }

        else if ($token == '&' && $next1 == '&'){
           echo '[ Relt_op, ' . '&&' . ' ]  ';
           // echo '<br>';
           $pula = true;
        }

        else if ($token == '<' && $next1 == '='){
           echo '[ Relt_op, ' . '<=' . ' ]  ';
           // echo '<br>';
           $pula = true;
        }

        else if ($token == '<'){
           echo '[ Relt_op, ' . $token . ' ]  ';
           // echo '<br>';
        }

        else if ($token == '>' && $next1 == '='){
           echo '[ Relt_op, ' . '>=' . ' ]  ';
           // echo '<br>';
           $pula = true;
        }

        else if ($token == '>'){
           echo '[ Relt_op, ' . $token . ']  ';
           // echo '<br>';
        }

        else if ($token == '['){
          echo '[ l_bracket, ' . $token . '] ,';
          // echo '<br>';
        }

        else if ($token == ']'){
          echo '[ r_bracket, ' . $token . '] ,';
          // echo '<br>';
        }

        else if ($token == '<' && $next1 == '>'){
           echo '[ Relt_op, ' . '<>' . ' ] ,';
           // echo '<br>';
           $pula = true;
        }

        else if ($token == '"'){
          $this->setContext('string');
          echo '[ string_literal, ';
        }

        else if ($token == '/*'){
          $this->setContext('comment');
        }

        else if ($token == '&' && $next1 != '&'){
          echo '[ pointer_addr, ' . $token . ' ] ,';
          // echo '<br>';
        }

        else if ($next1 == '[' && $next2 != ']'){
          echo '[ pointer_atrib, ' . $token . '[] ] ,';
          // echo '<br>';
        }

        else if (filter_var($token, FILTER_VALIDATE_FLOAT) || $token == '0'){
           echo '[ num, ' . $token . ' ] ,';
           // echo '<br>';
        }

        else if ($token == '*'){
          if(
            isset($this->ids[$context.'_'.$last1])
            ||
            filter_var($last1, FILTER_VALIDATE_FLOAT)
          ){
            echo '[ Arit_op, ' . $token . ' ]  ';
            // echo '<br>';
          }
          else{
            echo '[ pointer_atrib, ' . $token . ' ]  ';
            // echo '<br>';
          }

        }

        else{
          if(isset($this->ids[$context.'_'.$token])){
            $id = $this->ids[$context.'_'.$token];
          }
          else{
            if(isset($this->currentId[$context])){
              $id = $this->currentId[$context]++;
            }
            else{
              $id = $this->currentId[$context]=1;
            }
            $this->ids[$context.'_'.$token] = $id;
          }
          echo '[ ID' .$id . ', ' . $token . ' ]  ';
          // echo '<br>';
        }

      }

    }

}
