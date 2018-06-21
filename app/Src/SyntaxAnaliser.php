<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:20:48
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-14 17:11:26

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
  private $pos = 0;
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
      $this->pos = $tok['pos'];
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

  private function error($ret = false){
    if($ret){
      $this->err = true;
      return;
    }
    if($this->debug){echo '</br> in error';}

    // if($this->tok == ''){
    //   if(isset($this->tokens[0]['token'])){
    //     $tok = $this->tokens[0]['token'];
    //   }
    // }


    echo '<br>';
    echo 'Synax Error ';
    echo 'Unexpected token: \''. $this->tok . '\' ';
    echo 'in line: \''. $this->line. '\' column: \'' . $this->pos . '\'';

    exit();
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
    $this->Type($ret); $this->consume('ID');
  }

  private function VariableDecl($ret = false) {
    if($this->debug){echo '</br> in VariableDecl';}
    $this->Variable($ret); $this->consume(';');
  }

  private function Decl($ret = false) {
    if($this->debug){echo '</br> in Decl';}
    if($this->isClassDecl()){
      $this->ClassDecl($ret);
    }
    else if($this->isInterfaceDecl()){
      $this->InterfaceDecl($ret);
    }
    else if($this->isFunctionDecl()){
      $this->FunctionDecl($ret);
    }
    else if($this->isVariableDecl()){
      $this->VariableDecl($ret);
    }
    else{
      $this->error($ret);
    }
  }

  private function FunctionDecl($ret = false){
    if($this->debug){echo '</br> in FunctionDecl';}
    if ($this->tok == 'void'){
      $this->consume('void');
      $this->consume('ID');
      $this->consume('(');
      $this->Formals($ret);
      $this->consume(')');
      $this->StmtBlock($ret);
    }
    else {
      $this->Type($ret);
      $this->consume('ID');
      $this->consume('(');
      $this->Formals($ret);
      $this->consume(')');
      $this->StmtBlock($ret);
    }
  }

  private function Formals($ret = false){
    if($this->debug){echo '</br> in Formals';}
    if($this->isType()){
      $this->Variable($ret);
      $out = false;
      while(!$out) {
        if($this->tok == ','){
          $this->consume(',');
          $this->Variable($ret);
        }
        else{
          $out = true;
        }
      }
    }
    // pode ser VAZIO, por isso não gera erro
  }


  private function ClassDecl($ret = false) {
    if($this->debug){echo '</br> in ClassDecl';}
    if ($this->isClassDecl()) {
      $this->consume('class');
      $this->consume('ID');

      // pode oi não haver um extend ident
      if($this->tok == 'extend') {
        $this->consume('extend');
        $this->consume('ID');
      }

      // 0 ou N implements, ID
      $out = false;
      while(!out){
        if($this->tok == 'implements') {
          $this->consume('implements');
          $this->consume('ID');
          //0 ou N ,ID
          $out1 = false;
          while(!$out1){
            if($this->tok == ','){
              $this->consume(',');
              $this->consume('ID');
            }
            else{
              $out1 = true;
            }
          }
        }
        else{
          $out = true;
        }
      }

      $this->consume('{');

      // 0 ou N Field
      $out = false;
      while(!$out){
        if($this->isVariableDecl() || $this->isFunctionDecl()){
          $this->Field($ret);
        }
        else{
          $out = true;
        }
      }

      $this->consume('}');
    }
    else {
      $this->error($ret);
    }

  }

  private function Field($ret = false) {
    if($this->debug){echo '</br> in Field';}
    if ($this->isVariableDecl()){
      $this->VariableDecl($ret);
    }
    else if ($this->isFunctionDecl()){
      $this->FunctionDecl($ret);
    }
    else{
      $this->error($ret);
    }
  }

  private function InterfaceDecl($ret = false){
    if($this->debug){echo '</br> in InterfaceDecl';}
    if ($this->isInterfaceDecl()){
      $this->consume('interface');
      $this->consume('ID');
      $this->consume('{');
      // 0 ou N Prototype
      $out = false;
      while(!$out){
        if($this->isPrototype()){
          $this->Prototype($ret);
        }
        else{
          $out = true;
        }
      }
      $this->consume('}');
    }
    else{
      $this->error($ret);
    }
  }

  private function Prototype($ret = false){
    if($this->debug){echo '</br> in Prototype';}
    if($this->tok == 'void'){
      $this->consume('void');
      $this->consume('ID');
      $this->consume('(');
      $this->Formals($ret);
      $this->consume(')');
      $this->consume(';');
    }
    else{
      $this->Type($ret);
      $this->consume('ID');
      $this->consume('(');
      $this->Formals($ret);
      $this->consume(')');
      $this->consume(';');
    }
  }

  private function StmtBlock($ret = false){
    if($this->debug){echo '</br> in StmtBlock';}
    $this->consume('{');
    $out = false;
    //  0 ou N VariableDecl
    while(!$out){
      if($this->isVariableDecl()){
        $this->VariableDecl($ret);
      }
      else{
        $out = true;
      }
    }
    // 0 ou N Stmt
    $out = false;
    while(!$out){
      if($this->isStmt()){
        $this->Stmt($ret);
      }
      else{
        $out = true;
      }
    }
    $this->consume('}');
  }

  private function Stmt($ret = false){
    if($this->debug){echo '</br> in Stmt';}
    switch($this->tok){
      case 'if': {
        $this->IfStmt($ret);
        break;
      }
      case 'while': {
        $this->WhileStmt($ret);
        break;
      }
      case 'for': {
        $this->ForStmt($ret);
        break;
      }
      case 'break': {
        $this->BreakStmt($ret);
        break;
      }
      case 'return': {
        $this->ReturnStmt($ret);
        break;
      }
      case 'print': {
        $this->PrintStmt($ret);
        break;
      }
      case 'if': {
        $this->ifStmt($ret);
        break;
      }
      case '{': {
        $this->StmtBlock($ret);
        break;
      }
      default: {
        // if($this->isExpr()){
          $this->Expr(true);
          if($this->err){
            $this->err = false;
          }
        // }
        break;
      }
    }
  }

  private function IfStmt($ret = false){
    if($this->debug){echo '</br> in IfStmt';}
    $this->consume('if');
    $this->consume('(');
    $this->Expr($ret);
    $this->consume(')');
    $this->Stmt($ret);

    // 0 ou N Else
    $out = false;
    while(!$out){
      if($this->token == 'else'){
        $this->Stmt($ret);
      }
      else{
        $out = true;
      }
    }
  }

  private function WhileStmt($ret = false){
    if($this->debug){echo '</br> in WhileStmt';}
    $this->consume('(');
    $this->consume('while');
    $this->Expr($ret);
    $this->consume(')');
    $this->StmtBlock();
  }

  private function ForStmt($ret = false){
    if($this->debug){echo '</br> in ForStmt';}
    $this->consume('for');
    $this->consume('(');
    // 0 ou 1 Expr
    if($this->isExpr()){ $this->Expr($ret); }
    $this->consume(';');
    $this->Expr($ret);
    $this->consume(';');
    // 0 ou 1 Expr
    if($this->isExpr()){ $this->Expr($ret); }
    $this->consume(')');
    $this->Stmt($ret);
  }

  private function ReturnStmt($ret = false) {
    if($this->debug){echo '</br> in ReturnStmt';}
    $this->consume('return');
    // 0 ou 1 Expr
    if($this->isExpr()){ $this->Expr($ret); }
  }

  private function BreakStmt($ret = false) {
    if($this->debug){echo '</br> in BreakStmt';}
    $this->consume('break');
  }

  private function PrinsStmt($ret = false){
    if($this->debug){echo '</br> in PrinsStmt';}
    $this->consume('print');
    $this->consume('(');
    // 0 ou N Expr
    $out = false;
    while(!$out){
      if($this->isExpr()){
        $this->Expr($ret);
      }
      else{
        $out = true;
      }
    }
    $this->consume(')');
  }

  private function Expr($ret = false){
    if($this->debug){echo '</br> in Expr';}

      if($this->tok == 'this') {
        $this->consume('this');
        $this->Expr1($ret);
      }
      else if($this->tok == '(') {
        $this->consume('(');
        $this->Expr($ret);
        $this->consume(')');
        $this->Expr1($ret);
      }
      else if($this->tok == '-') {
        $this->consume('-');
        $this->Expr($ret);
        $this->Expr1($ret);
      }
      else if($this->tok == '!') {
        $this->consume('!');
        $this->Expr($ret);
        $this->Expr1($ret);
      }
      else if($this->tok == 'ReadInteger') {
        $this->consume('ReadInteger');
        $this->consume('(');
        $this->consume(')');
        $this->Expr1($ret);
      }
      else if($this->tok == 'ReadLine') {
        $this->consume('ReadLine');
        $this->consume('(');
        $this->consume(')');
        $this->Expr1($ret);
      }
      else if($this->tok == 'new') {
        $this->consume('ID');
        $this->Expr1($ret);
      }
      else if($this->tok == 'NewArray') {
        $this->consume('NewArray');
        $this->consume('(');
        $this->Expr($ret);
        $this->consume(',');
        $this->Type($ret);
        $this->consume(')');
        $this->Expr1($ret);
      }
      else if(
        $this->lexem == 'intConstant' ||
        $this->lexem == 'doubleConstant' ||
        $this->lexem == 'boolConstant' ||
        $this->lexem == 'stringConstant' ||
        $this->tok == 'null'
      ){
        $this->Constant($ret);
        $this->Expr1($ret);
      }
      else{



        if($this->isLvalue()){
          $this->LValue();
          $this->F1();
        }

        else if($this->isCall()){
          $this->Call();
          $this->Expr1();
        }

        else if($this->isConstant()){
          $this->Constant();
          $this->Expr1();
        }

        else{
          $this->error();
        }

      }

  }

  private function F1($ret = false){
    if($this->debug){echo '</br> in F1';}
    if($this->tok == '='){
      $this->consume('=');
      $this->Expr($ret);
      $this->Expr1($ret);
    }
    else{
      $this->Expr1($ret);
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
      }
      case '%':{
        $this->consume('%');
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

    // $indice = $this->indice + 1; // Token 2 a frente;
    //
    // $is = false;
    // if(isset($this->tokens[$indice])){
    //   if($this->tokens[$indice]['token'] == '['){
    //     $is = true;
    //   }
    // }

    if($this->lexem == 'ID'){
      $this->consume('ID');
    }

    else{
      $this->consume('ID');
      $this->Expr();
      $this->F2();
    }

  }

  private function F2(){
    if($this->debug){echo '</br> in F2';}
    switch ($this->tok) {
      case '.': {
        $this->consume('.');
        $this->consume('ID');
        break;
      }
      case '[': {
        $this->consume('[');
        $this->Expr();
        $this->consume(']');
        break;
      }
      default: {
        $this->error();
        break;
      }
    }
  }

  private function Call(){
    if($this->debug){echo '</br> in Call';}
    if($this->lexem == 'ID'){
      $this->consume('ID');
      $this->consume('(');
      $this->Actuals();
      $this->consume(')');
    }
    else if ($this->token == '('){
      $this->consume('(');
      $this->Actuals();
      $this->consume(')');
    }
    else{
      $this->Expr();
      $this->consume('.');
      $this->consume('ID');
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

  private function Constant(){
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


  //  HELPERS
  private function isType() {
    if($this->debug){echo '</br> in isType';}
    return (
      $this->tok == 'int' ||
      $this->tok == 'double' ||
      $this->tok == 'bool' ||
      $this->tok == 'string' ||
      $this->lexem == 'ID'
    );
  }

  private function isFunctionDecl() {
    if($this->debug){echo '</br> in isFunctionDecl';}
    $is = false;
    if ($this->isType() || $this->tok == 'void'){
      $indice = $this->indice + 2; // Token 2 a frente;
      if(isset($this->tokens[$indice])){
        if($this->tokens[$indice]['token'] == '('){
          $is = true;
        }
      }
    }
    return $is;
  }

  private function isPrototype(){
    if($this->debug){echo '</br> in isPrototype';}
    return $this->isFunctionDecl();
  }

  private function isClassDecl() {
    if($this->debug){echo '</br> in isClassDecl';}
    return $this->tok == 'class';
  }

  private function isVariableDecl(){
    if($this->debug){echo '</br> in isVariableDecl';}
    return $this->isVariable();
  }

  private function isVariable() {
    if($this->debug){echo '</br> in isVariable';}

    // return $this->isType();

    $is = false;


    if ($this->isType()){


      $indice = $this->indice + 2; // Token 2 a frente;

      if(isset($this->tokens[$indice])){
        if($this->tokens[$indice]['token'] == ';'){
          $is = true;
        }
      }

      $indice = $this->indice + 1; // Token 2 a frente;
      if(isset($this->tokens[$indice])){
        if($this->tokens[$indice]['token'] == '['){
          $is = true;
        }
      }

    }

    return $is;
  }

  private function isInterfaceDecl() {
    if($this->debug){echo '</br> in isInterfaceDecl';}
    return $this->tok == 'interface';
  }

  private function isIfStmt(){
    if($this->debug){echo '</br> in isIfStmt';}
    return $this->tok == 'if';
  }

  private function isWhileStmt(){
    if($this->debug){echo '</br> in isWhileStmt';}
    return $this->tok == 'while';
  }

  private function isForStmt(){
    if($this->debug){echo '</br> in isForStmt';}
    return $this->tok == 'for';
  }

  private function isReturnStmt(){
    if($this->debug){echo '</br> in isReturnStmt';}
    return $this->tok == 'return';
  }

  private function isBreakStmt(){
    if($this->debug){echo '</br> in isBreakStmt';}
    return $this->tok == 'break';
  }

  private function isStmtBlock(){
    if($this->debug){echo '</br> in isStmtBlock';}
    return $this->tok == '{';
  }

  private function isPrintStmt(){
    if($this->debug){echo '</br> in isPrintStmt';}
    return $this->tok == 'print';
  }

  private function isStmt(){
    if($this->debug){echo '</br> in isStmt';}
    return (
      $this->isIfStmt() ||
      $this->isWhileStmt() ||
      $this->isForStmt() ||
      $this->isBreakStmt() ||
      $this->isReturnStmt() ||
      $this->isPrintStmt() ||
      $this->isExpr() || // Duvida na interrogacao Expr?
      $this->isStmtBlock()
    );
  }

  private function isExpr(){
    if($this->debug){'</br> in isExpr';}
    return (
      $this->tok =='this' ||
      $this->tok == '-' ||
      $this->tok == '!' ||
      $this->tok == 'ReadInteger' ||
      $this->tok == 'ReadLine' ||
      $this->tok == 'new' ||
      $this->tok == 'NewArray' ||
      $this->tok == '(' ||
      $this->lexem == 'ID' ||
      $this->lexem == 'intConstant' ||
      $this->lexem == 'doubleConstant' ||
      $this->lexem == 'boolConstant' ||
      $this->lexem == 'stringConstant' ||
      $this->tok == 'null'
    );
  }

  // private function isExpr1(){
  //   $this->isExpr() ||
  // }


  private function isLvalue(){
    return(
      $this->lexem == 'ID'
      // ||
      // $this->isExpr()
    );
  }

  private function isConstant(){
    return(
      $this->lexem == 'intConstant' ||
      $this->lexem == 'boolConstant' ||
      $this->lexem == 'doubleConstant' ||
      $this->lexem == 'stringConstant' ||
      $this->lexem == 'null'
      //$this->isExpr()
    );
  }

  private function isCall(){
    return(
      $this->lexem == 'ID' ||
      $this->isExpr() ||
      $this->token = '(boolConstant)'
    );
  }

}
