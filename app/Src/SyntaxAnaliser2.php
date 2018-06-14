<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:20:48
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-14 16:14:16

    L =

    Progam          -> Decl+
    Decl            -> VariableDecl | FunctionDecl | ClassDecl | InterfaceDecl
    VariableDecl    -> Variable;
    Variable        -> Type ident
    Type            -> int Type1 | double Type1 | bool Type1 | string Type1 | ident Type1
    Type1           -> [] Type1
    FunctionDecla   -> Type ident ( Formals ) StmtBlock | void ident ( Formals ) StmtBlock
    Formals         -> Variable (, Variable)* | &
    ClassDecl       -> class ident (extentend ident)? (implements ident (,ident)*)* { Field* }
    Field           -> VariableDecl | FunctionDecl
    InterfaceDecl   -> interface ident  { Prototype* }
    Prototype       -> Type ident (Formals) ; | void ident (Formals) ;
    StmtBlock       -> { VariableDecl* Stmt* }
    Stmt            -> Expr? | IfStmt | WhileStmt | ForStmt | BreakStmt | ReturnStmt | PrintStmt | StmtBlock
    IfStmt          -> if (Expr) Stmt (else Stmt)*
    WhileStmt       -> while (Expr) StmtBlock
    ForStmt         -> for (Expr?; Expr; Expr?) Stmt
    ReturnStmt      -> return Expr?
    BreakStmt       -> break
    PrintStmt       -> print(Expr*)
    Expr            -> LValue F1 | Constant Expr1 | this Expr1 | Call Expr1 | (Expr) Expr1 | -Expr Expr1 | !Expr Expr1 | ReadInteger() Expr1 | ReadLine() Expr1 | new ident Expr1 | NewArray (Expr, Type) Expr1
    F1              -> = Expr Expr1 | Expr1
    Expr1           -> + Expr  Expr1 | - Expr  Expr1 | * Expr  Expr1 | / Expr  Expr1 | % Expr  Expr1 | < Expr  Expr1 |  <= Expr  Expr1 | > Expr  Expr1 | >= Expr  Expr1 | == Expr  Expr1 | != Expr  Expr1 | && Expr Expr1 | || Expr Expr1
    LValue          -> ident | Expr F2 | Expr F2
    F2              -> .ident | [Expr]
    Call            -> ident ( Actuals ) | Expr .ident | ( Actuals )
    Actuals         -> Expr (, Expr )* | &
    Constant        -> IntConstant | doubleConstant | boolConstatnt | stringConstant | null

 */


namespace App\Src;

use Exception;

class SyntaxAnaliser1
{

  // Variável global que armazena o token lido
  private $tok = '';
  private $lexem = '';
  private $tokens = [];
  private $indice = 0;
  private $debug = false;
  private $line = 0;
  private $column = 0;
  private $err = false;
  private $context = 0;
  private $exclude = [];

  function __construct($tokens = [], $debug = false){
    $this->setTokens($tokens);
    $this->debug = $debug;
  }

  public function start(){
    $tok = $this->advance();
    $this->Program($tok);
    echo 'Programa aceito';
  }

  public function setDebug($debug = false){
    $this->debug = $debug;
  }

  private function Program($tok){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Program()';}
    do{
      $this->Decl($tok);
    }
    while($tok != '');
    $this->context--;
  }

  private function setTokens($tokens = []){
    $this->tokens = $tokens;
  }

  private function advance() {
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'advance()';}
    $this->indice++;
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
    return $this->tok;
  }

  private function back() {
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'back()';}
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

  private function getToken(){
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'getToken()';}
    if(isset($this->tokens[$this->indice])){
      return $this->tokens[$this->indice];
    }
    return false;
  }

  private function consume($t){
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'consume() '. $t;}
    // Cosome token ID
    if($t == 'ID') {
      if($this->lexem == 'ID' ){
        return $this->advance();
      }
    }
    // Consome palavra reservada
    else if ($this->tok == $t){
      return $this->advance();
    }
    // Erro
    else{
      $this->error();
    }
  }

  private function error(){

    $this->err = true;

    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'error()';}
    echo ' Synax Error ';
    echo 'Unexpected token: \''. $this->tok . '\' ';
    echo 'Unexpected lexem: \''. $this->lexem . '\' ';
    echo 'in line: \''. $this->line. '\' column: \'' . $this->column . '\'';
    echo '<br>';

  }

  private function Type($tok = '') {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Type()';}
    switch($tok){
      case 'int': {
        $this->Type1($this->consume('int'));
        break;
      }
      case 'double': {
        $this->Type1($this->consume('double'));
        break;
      }
      case 'bool': {
        $this->Type1($this->consume('bool'));
        break;
      }
      case 'string': {
        $this->Type1($this->consume('string'));
        break;
      }
      default: {
        if($this->lexem == 'ID'){
          $this->Type1($this->consume('ID'););
        }
      }
    }
    $this->context--;
  }

  private function Type1($tok = '') {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Type1()';}
    switch ($tok) {
      case '[': {
        $this->consume('[');
        if($this->err) {return;}
        $t = $this->consume(']');
        if($this->err) {return;}
        $this->Type1($t);
        break;
      }
      default: {
        break;
      }
    }
    $this->context--;
  }

  private function Variable($tok = '') {
    $this->context++;
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Variable()';}
    $this->Type($tok);
    if($this->err){ return; }
    $t = $this->consume('ID');
    $this->context--;
  }

  private function VariableDecl($tok = '') {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'VariableDecl()';}
    $this->Variable($tok);
    if($this->err){ return; }
    $t = $this->consume(';');
    $this->context--;
  }

  private function Decl($tok = '') {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Decl()';}
    $this->ClassDecl($tok);
    if($this->err){
      $this->err = false;
      $this->InterfaceDecl($tok);
      if($this->err){
        $this->err = false;
        $this->FunctionDecl($tok);
        if($this->err){
          $this->err = false;
          $this->VariableDecl($tok);
        }
      }
    }
    $this->context--;
  }

  private function FunctionDecl($tok = ''){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'FunctionDecl()';}
    $t = $this->consume('void');
    if(!$this->err){
      $t = $this->consume('ID');
      if(!$this->err){
        $t = $this->consume('(');
        if(!$this->err){
          $this->Formals($t);
          if(!$this->err){
            $t = $this->consume(')');
            if(!$this->err){
              $this->StmtBlock($t);
            }
          }
        }
      }
    }
    if($this->err){
      $this->Type($tok);
      if(!$this->err){
        $t = $this->consume('ID');
        if(!$this->err){
          $t = $this->consume('(');
          if(!$this->err){
            $this->Formals($t);
            if(!$this->err){
              $t = $this->consume(')');
              if(!$this->err){
                $this->StmtBlock($t);
              }
            }
          }
        }
      }
    }
    $this->context--;
  }

  private function Formals($tok = ''){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Formals()';}

    $this->Variable($tok);
    while(!$this->err){
      $t = $this->consume(',');
      if(!$this->err){
        $this->Variable($t);
        if($this->err){
          return;
        }
      }
    }

    // pode ser VAZIO, por isso não gera erro
    $this->err = false;
    $this->context--;
  }

  private function ClassDecl($tok = '') {

    $this->context++;
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'ClassDecl()';}

    // Consome ID e class
    $t = $this->consume('ID');
    if($this->err){return;}
    $t = $this->consume('class');
    if($this->err){return;}

    // Consome exnted e ID
    $t = $this->consume('extend');
    if(!$this->err){
      $t = $this->consume('ID');
      if($this->err){
        return;
      }
    }

    $this->err = false;
    while(!$this->err){

      $t = $this->consume('implements');
      if(!$this->err){
        $t = $this->consume('ID');
        if($this->err){
          return;
        }
        else{
          while(!$this->err){
            $t = $this->consume(',');
            if(!$this->err){
              $t = $this->consume('ID');
              if($this->err){
                return;
              }
            }
          }
        }
      }

      $this->err = false;
      $t = $this->consume('{');
      if($this->err){ return ;}

      while(!$this->err && $t != '}'){
        $this->Field($t);
      }

      if(!$this->err){
        $this->consume('}');
      }

    }
    $this->context--;

  }

  private function Field($tok = '') {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Field()';}

    $this->VariableDecl($tok);
    if($this->err){
      $this->err = false;
      $this->FunctionDecl($tok);
    }
    $this->context--;
  }

  private function InterfaceDecl($tok = ''){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'InterfaceDecl()';}

    $t = $this->consume('interface');
    if($this->err) { return; }
    $t = $this->consume('ID');
    if($this->err) { return; }
    $t = $this->consume('{');
    if($this->err) { return; }

    while(!$this->err && $t !=  '}'){
        $this->Prototype($t);
    }

    if(!$this->err){
      $t = $this->consume('}');
    }
    $this->context--;
  }

  private function Prototype($tok = ''){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Prototype()';}

    $t = $this->consume('void');
    if(!$this->err){
      $t = $this->consume('ID');
      if($this->err){ return; }
      $t = $this->consume('(');
      if($this->err){ return; }
      $this->Formals($t);
      if($this->err){ return; }
      $t = $this->consume(')');
      if($this->err){ return; }
      $t = $this->consume(';');
      if($this->err){ return; }

    }
    else{
      $this->Type($tok);
      if($this->err){ return; }
      $t = $this->consume('ID');
      if($this->err){ return; }
      $t = $this->consume('(');
      if($this->err){ return; }
      $this->Formals($t);
      if($this->err){ return; }
      $t = $this->consume(')');
      if($this->err){ return; }
      $t = $this->consume(';');
    }
    $this->context--;
  }

  private function StmtBlock($tok = ''){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'StmtBlock()';}

    $t = $this->consume('{');
    if($this->err){ return; }

    while(!$this->err && $t != '}'){
      $this->VariableDecl($t);
      if($this->err){
        $this->err = false;
        $this->Stmt($t);
      }
    }

    if(!$this->err){
      $t = $this->consume('}');
    }
    $this->context--;
  }

  private function Stmt(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Stmt()';}
    $this->IfStmt();
    if($this->err){
      $this->err = false;
      $this->WhileStmt();
      if($this->err){
        $this->err = false;
        $this->ForStmt();
        if($this->err){
          $this->err = false;
          $this->BreakStmt();
          if($this->err){
            $this->err = false;
            $this->ReturnStmt();
            if($this->err){
              $this->err = false;
              $this->PrintStmt();
              if($this->err){
                $this->err = false;
                $this->IfStmt();
                if($this->err){
                  $this->err = false;
                  $this->StmtBlock();
                  if($this->err){
                    if($this->tok != ''){
                      $this->Expr();
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    $this->context--;
  }

  private function IfStmt(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'IfStmt()';}

    $this->consume('if');
    if($this->err){ return; }
    $this->consume('(');
    if($this->err){ return; }
    $this->Expr();
    if($this->err){ return; }
    $this->consume(')');
    if($this->err){ return; }
    $this->Stmt();
    if($this->err){ return; }

    while(!$this->err){
      $this->consume('else');
      if(!$this->err){
        $this->Stmt();
        if($this->err){ return; }
      }
    }

    $this->err = false;
    $this->context--;
  }

  private function WhileStmt(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'WhileStmt()';}
    $this->consume('(');
    if($this->err){ return; }
    $this->consume('while');
    if($this->err){ return; }
    $this->Expr();
    if($this->err){ return; }
    $this->consume(')');
    if($this->err){ return; }
    $this->StmtBlock();
    $this->context--;
  }

  private function ForStmt(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'ForStmt()';}

    $this->consume('for');
    if($this->err){ return; }
    $this->consume('(');
    if($this->err){ return;}

    if($this->tok != ';'){
      $this->Expr();
      if($this->err) { return; }
    }
    else{
      $this->consume(';');
      if($this->err) { return; }
      $this->Expr();
      if($this->err) { return; }
      $this->consume(';');
      if($this->err) { return; }
      if($this->tok != ')'){
        $this->Expr();
        if($this->err) { return; }
      }
      else{
        $this->consume(')');
        if($this->err) { return; }
        $this->Stmt();
      }
    }
    $this->context--;
  }

  private function ReturnStmt() {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'ReturnStmt()';}
    $this->consume('return');
    if($this->err){ return; }

    if($this->tok != ''){
      $this->Expr();
    }
    $this->context--;
  }

  private function BreakStmt() {
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'BreakStmt()';}
    $this->consume('break');
    $this->context--;
  }

  private function PrintStmt(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'PrinsStmt()';}

    $this->consume('print');
    if($this->err){ return; }
    $this->consume('(');
    if($this->err){ return; }

    while(!$this->err && $this->tok != ')'){
      $this->Expr();
    }

    if(!$this->err){
      $this->consume(')');
    }
    $this->context--;
  }

  private function Expr(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Expr()';}

    $this->consume('this');
    if(!$this->err){
      $this->Expr1();
      if($this->err){ return; }
    }
    else{
      $this->consume('(');
      if(!$this->err){
        $this->Expr();
        if($this->err) { return; }
        $this->consume(')');
        if($this->err) { return; }
        $this->Expr1();
        if($this->err) { return; }
      }
      else{
        $this->consume('-');
        if(!$this->err){
          $this->Expr();
          if($this->err) { return; }
          $this->Expr1();
          if($this->err) { return; }
        }
        else{
          $this->consume('!');
          if(!$this->err){
            $this->Expr();
            if($this->err) { return; }
            $this->Expr1();
            if($this->err) { return; }
          }
          else{
            $this->consume('ReadInteger');
            if(!$this->err){
              $this->consume('(');
              if($this->err) { return; }
              $this->consume(')');
              if($this->err) { return; }
              $this->Expr1();
              if($this->err) { return; }
            }
            else{
              $this->consume('ReadLine');
              if(!$this->err){
                $this->consume('(');
                if($this->err) { return; }
                $this->consume(')');
                if($this->err) { return; }
                $this->Expr1();
                if($this->err) { return; }
              }
              else{
                $this->consume('new');
                if(!$this->err){
                  $this->consume('ID');
                  if($this->err) { return; }
                  $this->Expr1();
                  if($this->err) { return; }
                }
                else{
                  $this->consume('NewArray');
                  if(!$this->err){
                    $this->consume('(');
                    if($this->err) { return; }
                    $this->Expr();
                    if($this->err) { return; }
                    $this->Type();
                    if($this->err) { return; }
                    $this->consume(')');
                    if($this->err) { return; }
                    $this->Expr1();
                    if($this->err) { return; }
                  }
                  else{
                    $this->Constant();
                    if(!$this->err){
                      $this->Expr1();
                      if($this->err) { return; }
                    }
                    else{
                      $this->LValue();
                      if(!$this->err){
                        $this->F1();
                        if($this->err) { return; }
                      }
                      else{
                        $this->Call();
                        if(!$this->err){
                          $this->Expr1();
                          if($this->err) { return; }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    $this->context--;
  }

  private function F1(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'F1()';}
    $this->consume('=');
    if(!$this->err){
      $this->Expr();
      if($this->err){ return ;}
      $this->Expr1();
      if($this->err){ return ;}
    }
    else{
      $this->Expr1();
      if($this->err){ return ;}
    }
    $this->context--;
  }

  private function Expr1(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Expr1()';}

    $this->consume('+');
    if(!$this->err){
      $this->Expr();
      if($this->err){ return; }
      $this->Expr1();
      if($this->err){ return; }
    }
    else{
      $this->consume('-');
      if(!$this->err){
        $this->Expr();
        if($this->err){ return; }
        $this->Expr1();
        if($this->err){ return; }
      }
      else{
        $this->consume('*');
        if(!$this->err){
          $this->Expr();
          if($this->err){ return; }
          $this->Expr1();
          if($this->err){ return; }
        }
        else{
          $this->consume('/');
          if(!$this->err){
            $this->Expr();
            if($this->err){ return; }
            $this->Expr1();
            if($this->err){ return; }
          }
          else{
            $this->consume('%');
            if(!$this->err){
              $this->Expr();
              if($this->err){ return; }
              $this->Expr1();
              if($this->err){ return; }
            }
            else{
              $this->consume('<');
              if(!$this->err){
                $this->Expr();
                if($this->err){ return; }
                $this->Expr1();
                if($this->err){ return; }
              }
              else{
                $this->consume('<=');
                if(!$this->err){
                  $this->Expr();
                  if($this->err){ return; }
                  $this->Expr1();
                  if($this->err){ return; }
                }
                else{
                  $this->consume('>');
                  if(!$this->err){
                    $this->Expr();
                    if($this->err){ return; }
                    $this->Expr1();
                    if($this->err){ return; }
                  }
                  else{
                    $this->consume('>=');
                    if(!$this->err){
                      $this->Expr();
                      if($this->err){ return; }
                      $this->Expr1();
                      if($this->err){ return; }
                    }
                    else{
                      $this->consume('==');
                      if(!$this->err){
                        $this->Expr();
                        if($this->err){ return; }
                        $this->Expr1();
                        if($this->err){ return; }
                      }
                      else{
                        $this->consume('!=');
                        if(!$this->err){
                          $this->Expr();
                          if($this->err){ return; }
                          $this->Expr1();
                          if($this->err){ return; }
                        }
                        else{
                          $this->consume('&&');
                          if(!$this->err){
                            $this->Expr();
                            if($this->err){ return; }
                            $this->Expr1();
                            if($this->err){ return; }
                          }
                          else{
                            $this->consume('||');
                            if(!$this->err){
                              $this->Expr();
                              if($this->err){ return; }
                              $this->Expr1();
                              if($this->err){ return; }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    // Pode ser VAZIO
    $this->err = false;
    $this->context--;
  }

  private function LValue(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'LValue()';}
    $this->consume('ID');
    if($this->err){
      $this->Expr();
      if($this->err){ return; }
      $this->F2();
      if($this->err){ return; }
    }
    $this->context--;
  }

  private function F2(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'F2()';}
    $this->consume('.');
    if(!$this->err){
      $this->consume('ID');
      if($this->err){ return; }
    }
    else{
      $this->consume('[');
      if($this->err){ return; }
      $this->Expr();
      if($this->err){ return; }
      $this->consume(']');
      if($this->err){ return; }
    }
    $this->context--;
  }

  private function Call(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Call()';}
    $this->consume('ID');
    if(!$this->err){
      $this->consume('(');
      if($this->err){ return; }
      $this->Actuals();
      if($this->err){ return; }
      $this->consume(')');
      if($this->err){ return; }
    }
    else{
      // $this->Expr();
      // if(!$this->err){
      //   $this->consume('.');
      //   if($this->err){ return; }
      //   $this->consume('ID');
      //   if($this->err){ return; }
      // }
      // else{
        $this->consume('(');
        if($this->err){ return; }
        $this->Actuals();
        if($this->err){ return; }
        $this->consume(')');
        if($this->err){ return; }
      // }
    }
    $this->context--;
  }

  private function Actuals(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Actuals()';}
    $this->Expr();
    while(!$this->err){
      $this->consume(',');
      if(!$this->err){
        $this->Expr();
        if($this->err){
          return;
        }
      }
    }
    $this->err = false;
    $this->context--;
  }

  private function Constant(){
    $this->context++;    
    if($this->debug){echo '</br>' . str_repeat('-', $this->context) . 'Constant()';}
    switch($this->lexem){
      case 'intConstant': {
        $this->consume($this->tok);
        if($this->err){ return; }
        break;
      }
      case 'boolConstant': {
        $this->consume($this->tok);
        if($this->err){ return; }
        break;
      }
      case 'doubleConstant': {
        $this->consume($this->tok);
        if($this->err){ return; }
        break;
      }
      case 'stringConstant': {
        $this->consume($this->tok);
        if($this->err){ return; }
        break;
      }
      case 'null': {
        $this->consume($this->tok);
        if($this->err){ return; }
        break;
      }
      default: {
        if($this->err){ return; }
      }
    }
    $this->context--;
  }
}