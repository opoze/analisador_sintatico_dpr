<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:20:48
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-13 16:53:04

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

class SyntaxAnaliser
{

  // Variável global que armazena o token lido
  private $tok = '';
  private $lexem = '';
  private $tokens = [];
  private $indice = -1;
  private $debug = false;
  private $line = 0;
  private $column = 0;
  private $err = false;

  function __construct($tokens = [], $debug = false){
    $this->setTokens($tokens);
    $this->debug = $debug;
  }

  public function start(){
    $this->advance();
    $this->Program();
    echo 'Programa aceito';
  }

  public function setDebug($debug = false){
    $this->debug = $debug;
  }

  private function Program(){
    if($this->debug){echo '</br> in Program';}
    do{
      $this->Decl();
    }
    while($this->tok != '');
  }

  private function setTokens($tokens = []){
    $this->tokens = $tokens;
  }

  private function advance() {
    if($this->debug){echo '</br> in advance';}
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
    if($this->debug){echo '</br> in getToken';}
    $this->indice++;
    if(isset($this->tokens[$this->indice])){
      // echo '<pre>';
      // var_dump($this->tokens[$this->indice]);
      // echo '</pre>';
      return $this->tokens[$this->indice];
    }
    return false;
  }

  private function consume($t){
    if($this->debug){echo '</br> in consume';}
    // Cosome token ID
    if($t == 'ID') {
      if($this->lexem == 'ID' ){
        $this->advance();
      }
    }
    // Consome palavra reservada
    else if ($this->tok == $t){
      $this->advance();
    }
    // Erro
    else{
      $this->error();
    }
  }

  private function error(){
    $this->err = true;
    if($this->debug){echo '</br> in error';}
    // echo '<br>';
    // echo 'Synax Error ';
    // echo 'Unexpected token: \''. $this->tok . '\' ';
    // echo 'in line: \''. $this->line. '\' column: \'' . $this->column . '\'';
  }

  private function Type($ret = false) {
    if($this->debug){echo '</br> in Type';}
    switch($this->tok){
      case 'int': {
        $this->consume('int');
        $this->Type1($ret);
        break;
      }
      case 'double': {
        $this->consume('double');
        $this->Type1($ret);
        break;
      }
      case 'bool': {
        $this->consume('bool');
        $this->Type1($ret);
        break;
      }
      case 'string': {
        $this->consume('string');
        $this->Type1($ret);
        break;
      }
      default: {
        if($this->lexem == 'ID'){
          $this->consume('ID');
          $this->Type1($ret);
        }
        else{
          $this->error($ret);
        }
        break;
      }
    }
  }

  private function Type1($ret = false) {
    if($this->debug){echo '</br> in Type1';}
    switch ($this->tok) {
      case '[': {
        $this->consume('['); $this->consume(']'); $this->Type1($ret);
        break;
      }
      default: {
        // pode ser VAZIO
        // $this->error();
        break;
      }
    }
  }

  private function Variable($ret = false) {
    if($this->debug){echo '</br> in Variable';}
    $this->Type($ret);
    if($this->err){ return; }
    $this->consume('ID');
  }

  private function VariableDecl($ret = false) {
    if($this->debug){echo '</br> in VariableDecl';}
    $this->Variable();
    if($this->err){ return; }
    $this->consume(';');
  }

  private function Decl() {
    if($this->debug){echo '</br> in Decl';}
    $this->ClassDecl();
    if($this->err){
      $this->err = false;
      $this->InterfaceDecl();
      if($thi->err){
        $this->err = false;
        $this->FunctionDecl();
        if($this->err){
          $this->err = false;
          $this->VariableDecl();
        }
      }
    }
  }

  private function FunctionDecl(){
    if($this->debug){echo '</br> in FunctionDecl';}
    $this->consume('void');
    if(!$this->err){
      $this->consume('ID');
      if(!$this->err){
        $this->consume('(');
        if(!$this->err){
          $this->Formals();
          if(!$this->err){
            $this->consume(')');
            if(!$this->err){
              $this->StmtBlock();
            }
          }
        }
      }
    }
    if($this->err){
      $this->Type();
      if(!$this->err){
        $this->consume('ID');
        if(!$this->err){
          $this->consume('(');
          if(!$this->err){
            $this->Formals();
            if(!$this->err){
              $this->consume(')');
              if(!$this->err){
                $this->StmtBlock();
              }
            }
          }
        }
      }
    }
  }

  private function Formals(){
    if($this->debug){echo '</br> in Formals';}

    $this->Variable();
    while(!$this->err){
      $this->consume(',');
      if(!$this->err){
        $this->Variable();
        if($this->err){
          return;
        }
      }
    }

    // pode ser VAZIO, por isso não gera erro
    $this->err = false;
  }

  private function ClassDecl() {

    if($this->debug){echo '</br> in ClassDecl';}

    // Consome ID e class
    $this->consume('ID');
    if($this->err){return;}
    $this->consume('class');
    if($this->err){return;}

    // Consome exnted e ID
    $this->consume('extend');
    if(!$this->err){
      $this->consume('ID');
      if($this->err){
        return;
      }
    }

    $this->err = false;
    while(!$this->err){

      $this->consume('implements');
      if(!$this->err){
        $this->consume('ID');
        if($this->err){
          return;
        }
        else{
          while(!$this->err){
            $this->consume(',');
            if(!$this->err){
              $this->consume('ID');
              if($this->err){
                return;
              }
            }
          }
        }
      }

      $this->err = false;
      $this->consume('{');
      if($this->err){ return ;}

      while(!$this->err && $this->tok != '}'){
        $this->Field();
      }

      if(!$this->err){
        $this->consume('}');
      }

    }

  }

  private function Field() {
    if($this->debug){echo '</br> in Field';}

    $this->VariableDecl();
    if($this->err){
      $this->err = false;
      $this->FunctionDecl();
    }

  }

  private function InterfaceDecl(){
    if($this->debug){echo '</br> in InterfaceDecl';}

    $this->consume('interface');
    if($this->err) { return; }
    $this->consume('ID');
    if($this->err) { return; }
    $this->consume('{');
    if($this->err) { return; }

    while(!$this->err && $this->tok !=  '}'){
        $this->Prototype();
    }

    if(!$this->err){
      $this->consume('}');
    }

  }

  private function Prototype(){
    if($this->debug){echo '</br> in Prototype';}

    $this->consume('void');
    if(!$this->err){
      $this->consume('ID');
      if($this->err){ return; }
      $this->consume('(');
      if($this->err){ return; }
      $this->Formals();
      if($this->err){ return; }
      $this->consume(')');
      if($this->err){ return; }
      $this->consume(';');
      if($this->err){ return; }

    }
    else{
      $this->Type();
      if($this->err){ return; }
      $this->consume('ID');
      if($this->err){ return; }
      $this->consume('(');
      if($this->err){ return; }
      $this->Formals();
      if($this->err){ return; }
      $this->consume(')');
      if($this->err){ return; }
      $this->consume(';');
    }

  }

  private function StmtBlock(){
    if($this->debug){echo '</br> in StmtBlock';}

    $this->consume('{');
    if($this->err){ return; }

    while(!$this->err && $this->tok != }){
      $this->VariableDecl();
      if($this->err){
        $this->err = false;
        $this->Stmt();
      }
    }

    if(!$this->err){
      $this->consume('}');
    }

  }

  private function Stmt(){
    if($this->debug){echo '</br> in Stmt';}
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
                $this->ifStmt();
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
  }

  private function IfStmt(){
    if($this->debug){echo '</br> in IfStmt';}

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

  }

  private function WhileStmt(){
    if($this->debug){echo '</br> in WhileStmt';}
    $this->consume('(');
    if($this->err){ return; }
    $this->consume('while');
    if($this->err){ return; }
    $this->Expr();
    if($this->err){ return; }
    $this->consume(')');
    if($this->err){ return; }
    $this->StmtBlock();
  }

  private function ForStmt(){
    if($this->debug){echo '</br> in ForStmt';}

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

  }

  private function ReturnStmt() {
    if($this->debug){echo '</br> in ReturnStmt';}
    $this->consume('return');
    if($this->err){ return; }

    if($this->tok != ''){
      $this->Expr();
    }

  }

  private function BreakStmt() {
    if($this->debug){echo '</br> in BreakStmt';}
    $this->consume('break');
  }

  private function PrinsStmt(){
    if($this->debug){echo '</br> in PrinsStmt';}

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

  }

  private function Expr(){
    if($this->debug){echo '</br> in Expr';}

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
  }

  private function F1(){
    if($this->debug){echo '</br> in F1';}

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

  }

  private function Expr1($ret = false){
    if($this->debug){echo '</br> in Expr1';}

    

    switch ($this->tok) {
      case '+':{
        $this->consume('+');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '-':{
        $this->consume('-');
        $this->Expr1($ret);
        $this->Expr($ret);
        break;
      }
      case '*':{
        $this->consume('*');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '/':{
        $this->consume('/');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
        $this->consume('%');
      }
      case '%':{
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '<':{
        $this->consume('<');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '<=':{
        $this->consume('<=');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '>':{
        $this->consume('>');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '>=':{
        $this->consume('>=');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '==':{
        $this->consume('==');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '!=':{
        $this->consume('!=');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '&&':{
        $this->consume('&&');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      case '||':{
        $this->consume('||');
        $this->Expr($ret);
        $this->Expr1($ret);
        break;
      }
      default:{
        //$this->error();
        //pode ser vazio
        break;
      }
    }
  }

  private function LValue($ret = false){
    if($this->debug){echo '</br> in LValue';}
    if($this->lexem == 'ID'){
      $this->consume('ID');
    }
    else{
      $this->Expr($ret);
      $this->F2($ret);
    }
  }

  private function F2($ret = false){
    if($this->debug){echo '</br> in F2';}
    switch ($this->tok) {
      case '.': {
        $this->consume('.');
        $this->consume('ID');
        break;
      }
      case '[': {
        $this->consume('[');
        $this->Expr($ret);
        $this->consume(']');
        break;
      }
      default: {
        $this->error($ret);
        break;
      }
    }
  }

  private function Call($ret = false){
    if($this->debug){echo '</br> in Call';}
    if($this->lexem == 'ID'){
      $this->consume('ID');
      $this->consume('(');
      $this->Actuals($ret);
      $this->consume(')');
    }
    else if ($this->token == '('){
      $this->Expr($ret);
      $this->consume('.');
      $this->consume('ID');
    }
    else{
      $this->consume('(');
      $this->Actuals($ret);
      $this->consume(')');
    }
  }

  private function Actuals($ret = false){
    if($this->debug){echo '</br> in Actuals';}
    if($this->isExpr){
      $this->Expr($ret);
      $out = false;
      while(!$out) {
        if($this->tok == ','){
          $this->consume(',');
          $this->Expr($ret);
        }
        else{
          $out = true;
        }
      }
    }
    // pode ser VAZIO então não da erro caso não seja Expr
  }

  private function Constant($ret = false){
    if($this->debug){echo '</br> in Constant';}
    switch($this->lexem){
      case 'intConstant': {
        $this->consume($this->tok);
        break;
      }
      case 'boolConstant': {
        $this->consume($this->tok);
        break;
      }
      case 'doubleConstant': {
        $this->consume($this->tok);
        break;
      }
      case 'stringConstant': {
        $this->consume($this->tok);
        break;
      }
      case 'null': {
        $this->consume($this->tok);
        break;
      }
      default: {
        $this->error($ret);
      }
    }
  }
