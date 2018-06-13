<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:16:57
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-13 15:22:15
 */

namespace App\Src;

use Exception;
use App\Src\Eregs;

class TokenizerDecaf
{

    private $tokens = [];
    private $eregs = null;
    private $ids = [];
    private $currentId = [];
    private $context = '';
    private $lexemas = [];
    private $tkns = [];

    private $reservedWords = [
      'int',
      'double',
      'bool',
      'string',
      'void',
      'class',
      'extend',
      'implements',
      'interface',
      'if',
      'else',
      'while',
      'for',
      'return',
      'break',
      'print',
      'this',
      'ReadInteger',
      'ReadLine',
      'new',
      'NewArray'
      'null',
      'intConstant',
      'doubleConstant',
      'boolConstatnt',
      'stringConstant',
      'Print'
    ];

    function __construct(){
      $this->eregs = new Eregs();
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
      return $this->tkns;
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

      $next1 = false;
      $next2 = false;
      $string = '';
      $last1 = false;
      $last2 = false;
      $pula = false;

      foreach ($this->tokens as $key => $token) {

        // Somente para impressao na tela
        if($token=='##'){
          //echo '<br>';
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
            $this->tkns[]['string'] = $string;
            $string = '';
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
          $this->tkns[]['reserved_word'] = $token;
        }

        else if($token == ','){
           $this->tkns[]['comma'] = $token;
        }

        else if($token == ';'){
           $this->tkns[]['semicolon'] = $token;
        }

        else if($token == '{'){
           $this->tkns[]['l_cbrace'] = $token;
        }

        else if($token == '}'){
           $this->tkns[]['r_cbrace'] = $token;
        }

        else if($token == '('){
           $this->tkns[]['l_parent'] = $token;
        }

        else if($token == ')'){
           $this->tkns[]['r_parent'] = $token;
        }

        else if($token == '<' && $next2 == '>'){
           $this->tkns[]['include'] = $token;
        }

        else if($token == '>' && $last2 == '<'){
           $this->tkns[]['include'] = $token;
        }

        else if($last1 == '<' && $next1 == '>'){
           $this->tkns[]['include'] = $token;
        }

        else if ($token == '=' && $next1 == '='){
           $this->tkns[]['Relat_op'] = '==';
           $pula = true;
        }

        else if ($token == '='){
           $this->tkns[]['Equal_op'] = $token;
        }

        else if ($token == '+' && $next1 == '+'){
           $this->tkns[]['Inc_op'] = '++';
           $pula = true;
        }

        else if ($token == '+'){
           $this->tkns[]['Arit_op'] = $token;
        }

        else if ($token == '-'){
           $this->tkns[]['Arit_op'] = $token;
        }

        else if ($token == '&' && $next1 == '&'){
           $this->tkns[]['Relat_op'] = '&&';
           $pula = true;
        }

        else if ($token == '<' && $next1 == '='){
           $this->tkns[]['Relat_op'] = '<=';
           $pula = true;
        }

        else if ($token == '<'){
           $this->tkns[]['Relat_op'] = $token;
        }

        else if ($token == '>' && $next1 == '='){
           $this->tkns[]['Relat_op'] = '>=';
           $pula = true;
        }

        else if ($token == '>'){
           $this->tkns[]['Relat_op'] = $token;
        }

        else if ($token == '['){
          $this->tkns[]['l_bracket'] = $token;
        }

        else if ($token == ']'){
          $this->tkns[]['r_bracket'] = $token;
        }

        else if ($token == '<' && $next1 == '>'){
           $this->tkns[]['Relat_op'] = '<>';
           $pula = true;
        }

        else if ($token == '"'){
          $this->setContext('string');
        }

        else if ($token == '/*'){
          $this->setContext('comment');
        }

        else if ($token == '&' && $next1 != '&'){
          $this->tkns[]['pointer_addr'] = '&';
        }

        else if ($next1 == '[' && $next2 != ']'){
          $this->tkns[]['pointer_atrib'] = $token . '[]';
        }

        else if (filter_var($token, FILTER_VALIDATE_FLOAT) || $token == '0'){
           $this->tkns[]['num'] = $token;
        }

        else if ($token == '*'){
          if(
            isset($this->ids[$context.'_'.$last1])
            ||
            filter_var($last1, FILTER_VALIDATE_FLOAT)
          ){
            $this->tkns[]['Arit_op'] = $token;
          }
          else{
            $this->tkns[]['pointer_atrib'] = $token;
          }

        }
        else{
          $this->tkns[]['ID'] = $token;
        }

      }

    }

}
