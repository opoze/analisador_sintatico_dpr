<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:16:57
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-14 17:12:58
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
    private $tokenInfo = [];

    private $line = 1;

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
      'NewArray',
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
              $this->line++;
          }
          fclose($handle);
          $this->lexemas();
      } else {
        return 'Error loading file';
      }
      return $this->tkns;
    }

    private function proccessLine($line = ''){

      $this->originalLine = $line;

      $line = trim($line);
      if(strlen($line) > 0 ){

        $str = $this->removeLineComment($line);

        $words = explode(' ', $str);
        foreach ($words as $word) {
          $this->split($word);
        }

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

                //Local do token na linha original
                $pos = strpos($this->originalLine, $keyword1[0]);

                $this->tokenInfo[] = [
                  'line' => $this->line,
                  'pos' => $pos + 1
                ];
              }
            }
          }
        }

        $end = $start + $len;
        if($len > 0){
          // echo htmlspecialchars($keyword[0]) . '<br>';
          $this->tokens[] = $keyword[0];

          $pos = strpos($this->originalLine, $keyword[0]);
          $this->tokenInfo[] = [
            'line' => $this->line,
            'pos' => $pos + 1
          ];
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
            $this->tkns[] = [
              'lexem' => 'stringConstant',
              'token' => $string,
              'line' => $this->tokenInfo[$key]['line'],
              'pos' => $this->tokenInfo[$key]['pos']
            ];
            $string = '';
            $this->setContext('');
          }
          else{
            $string .= ' '.$token;
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
          $this->tkns[] = [
            'lexem' => 'reserved_word',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == ','){
          $this->tkns[] = [
            'lexem' => 'comma',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == ';'){
          $this->tkns[] = [
            'lexem' => 'semicolon',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == '{'){
          $this->tkns[] = [
            'lexem' => 'l_cbrace',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == '}'){
          $this->tkns[] = [
            'lexem' => 'r_cbrace',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == '('){
           $this->tkns[] = [
            'lexem' => 'l_parent',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == ')'){
           $this->tkns[] = [
            'lexem' => 'r_parent',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == '<' && $next2 == '>'){
          $this->tkns[] = [
            'lexem' => 'include',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == '>' && $last2 == '<'){
          $this->tkns[] = [
            'lexem' => 'include',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($last1 == '<' && $next1 == '>'){
          $this->tkns[] = [
            'lexem' => 'include',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '=' && $next1 == '='){
          $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => '==',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
           $pula = true;
        }

        else if ($token == '='){
          $this->tkns[] = [
            'lexem' => 'Equal_op',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '+' && $next1 == '+'){
          $this->tkns[] = [
            'lexem' => 'Inc_op',
            'token' => '++',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
          $pula = true;
        }

        else if ($token == '+'){
          $this->tkns[] = [
            'lexem' => 'Arit_op',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '-'){
          $this->tkns[] = [
            'lexem' => 'Arit_op',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '&' && $next1 == '&'){
           $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => '&&',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
           $pula = true;
        }

        else if ($token == '<' && $next1 == '='){
           $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => '<=',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
           $pula = true;
        }

        else if ($token == '<'){
           $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '>' && $next1 == '='){
           $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => '>=',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
           $pula = true;
        }

        else if ($token == '>'){
           $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '['){
          $this->tkns[] = [
            'lexem' => 'l_bracket',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == ']'){
          $this->tkns[] = [
            'lexem' => 'r_bracket',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '<' && $next1 == '>'){
           $this->tkns[]['Relat_op'] = '<>';
           $this->tkns[] = [
            'lexem' => 'Relat_op',
            'token' => '<>',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
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
          $this->tkns[] = [
            'lexem' => 'pointer_addr',
            'token' => '&',
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($next1 == '[' && $next2 != ']'){
          $this->tkns[] = [
            'lexem' => 'ID',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if (filter_var($token, FILTER_VALIDATE_FLOAT) || $token == '0'){
          $this->tkns[] = [
            'lexem' => 'doubleConstant',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if (filter_var($token, FILTER_VALIDATE_INT) || $token == '0'){
          $this->tkns[] = [
            'lexem' => 'intConstant',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == 'null'){
          $this->tkns[] = [
            'lexem' => 'null',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if($token == 'true' || $token == 'false'){
          $this->tkns[] = [
            'lexem' => 'boolConstant',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

        else if ($token == '*'){
          if(
            isset($this->ids[$context.'_'.$last1])
            ||
            filter_var($last1, FILTER_VALIDATE_FLOAT)
          ){
            $this->tkns[] = [
              'lexem' => 'Arit_op',
              'token' => $token,
              'line' => $this->tokenInfo[$key]['line'],
              'pos' => $this->tokenInfo[$key]['pos']
            ];
          }
          else{
            $this->tkns[] = [
              'lexem' => 'pointer_atrib',
              'token' => $token,
              'line' => $this->tokenInfo[$key]['line'],
              'pos' => $this->tokenInfo[$key]['pos']
            ];
          }

        }
        else{
          $this->tkns[] = [
            'lexem' => 'ID',
            'token' => $token,
            'line' => $this->tokenInfo[$key]['line'],
            'pos' => $this->tokenInfo[$key]['pos']
          ];
        }

      }

    }

}
