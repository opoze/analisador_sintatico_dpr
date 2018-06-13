<?php

/**
 * @Author: Luís Alberto Zagonel Pozenato
 * @Date:   2018-06-13 15:20:48
 * @Last Modified by:   Luís Alberto Zagonel Pozenato
 * @Last Modified time: 2018-06-13 15:22:04
    
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

class Semantic
{
  // Variável global que armazena o token lido
  private $tok = '';
  private $lexem = '';
  private $tokens = [];
  private $indice = -1;

  private function setTokens($tokens = []){
    $this->tokens = $tokens;
  }

  private function advance() {
    // token -> lexem;
    $tok = $this->getToken();
    if(is_array($tok)){
      $this->lexem = key($tok);
      $this->tok = $tok[$this->lexem];
    }
    else{
      $this->tok = '';
    }
  }

  private function getToken(){
    $this->indice++;
    if(isset($this->tokens[$this->indice])){
      return $this->tokens[$this->indice];
    }
    return false;
  }

  private function consume($t){
    // Cosome token ID
    if($t == 'ID') {
      if(key($this->tok)){
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
    // throw exception
  }

  private function Type() {
    switch($this->tok){
      case 'int': {
        $this->consume('int');
        $this->Type1();
        break;
      }
      case 'double': {
        $this->consume('double');
        $this->Type1();
        break;
      }
      case 'bool': {
        $this->consume('bool');
        $this->Type1();
        break;
      }
      default: {
        if($this->lexem == 'ID'){
          $this->consume('ID');
          $this->Type1();
        }
        else{
          $this->error();
        }
        break;
      }
    }
  }

  private function Type1() {
    switch ($this->tok) {
      case '[': {
        $this->consume('['); $this->consume(']'); $this->Type1();
        break;
      }
      default: {
        // pode ser VAZIO
        // $this->error();
        break;
      }
    }
  }

  private function Variable() {
    $this->Type(); $this->consume('ID');
  }

  private function VariableDecl() {
    $this->Variable(); $this->consume(';');
  }

  private function Decl() {
    if($this->isClassDecl()){
      $this->ClassDecl();
    }
    else if($this->isInterfaceDecl()){
      $this->InterfaceDecl();
    }
    else if(isFunctionDecl()){
      $this->FunctionDecl();
    }
    else if(isVariableDecl()){
      $this->VariableDecl();
    }
    else{
      $this->error();
    }
  }

  public function Program(){
    do{
      $this->Decl();
    }
    while($this->tok != '');
  }

  private function FunctionDecl(){
    if ($this->tok == 'void'){
      $this->consume('void');
      $this->consume('ID');
      $this->consume('(');
      $this->Formals();
      $this->consume(')');
      $this->StmtBlock();
    }
    else (){
      $this->Type();
      $this->consume('ID');
      $this->consume('(');
      $this->Formals();
      $this->consume(')');
      $this->StmtBlock();
    }
  }

  private function Formals(){
    if($this->Type()){
      $this->Variable();
      $out = false;
      while(!$out) {
        if($this->tok == ','){
          $this->consume(',');
          $this->Variable();
        }
        else{
          $out = true;
        }
      }
    }
    // pode ser VAZIO, por isso não gera erro
  }


  private function ClassDecl() {
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
          $this->Field();
        }
        else{
          $out = true;
        }
      }

      $this->consume('}');
    }
    else {
      $this->error();
    }

  }

  private function Field() {
    if ($this->isVariableDecl()){
      $this->VariableDecl();
    }
    else if ($this->isFunctionDecl()){
      $this->FunctionDecl();
    }
    else{
      $this->error();
    }
  }

  private function InterfaceDecl(){
    if ($this->isInterfaceDecl()){
      $this->consume('interface');
      $this->consume('ID');
      $this->consume('{');
      // 0 ou N Prototype
      $out = false;
      while(!$out){
        if($this->isPrototype()){
          $this->Prototype();
        }
        else{
          $out = true;
        }
      }
      $this->consume('}');
    }
    else{
      $this->error();
    }
  }

  private function Prototype(){
    if($this->tok == 'void'){
      $this->consume('void');
      $this->consume('ID');
      $this->consume('(');
      $this->Formals();
      $this->consume(')');
      $this->consume(';');
    }
    else{
      $this->Type();
      $this->consume('ID');
      $this->consume('(');
      $this->Formals();
      $this->consume(')');
      $this->consume(';');
    }
  }

  private function StmtBlock(){
    $this->consume('{');
    $out = false;
    //  0 ou N VariableDecl
    while(!$out){
      if($this->isVariableDecl()){
        $this->VariableDecl();
      }
      else{
        $out = true;
      }
    }
    // 0 ou N Stmt
    $out = false;
    while(!$out){
      if($this->isStmt()){
        $this->Stmt();
      }
      else{
        $out = true;
      }
    }
    $this->consume('}');
  }

  private function Stmt(){
    switch($this->tok){
      case 'if': {
        $this->IfStmt();
        break;
      }
      case 'while': {
        $this->WhileStmt();
        break;
      }
      case 'for': {
        $this->ForStmt();
        break;
      }
      case 'break': {
        $this->BreakStmt();
        break;
      }
      case 'return': {
        $this->ReturnStmt();
        break;
      }
      case 'print': {
        $this->PrintStmt();
        break;
      }
      case 'if': {
        $this->ifStmt();
        break;
      }
      case '{': {
        $this->StmtBlock();
        break;
      }
      default: {
        if($this->isExpr()){
          $this->Expr();
        }
        break;
      }
    }
  }

  private function IfStmt(){
    $this->consume('if');
    $this->consume('(');
    $this->Expr();
    $this->consume(')');
    $this->Stmt();

    // 0 ou N Else
    $out = false;
    while(!$out){
      if($this->token == 'else'){
        $this->Stmt();
      }
      else{
        $out = true;
      }
    }
  }

  private function WhileStmt(){
    $this->consume('while');
    $this->consume('(');
    $this->Expr();
    $this->consume(')');
    $this->StmtBlock();
  }

  private function ForStmt(){
    $this->consume('for');
    $this->consume('(');
    // 0 ou 1 Expr
    if($this->isExpr()){ $this->Expr(); }
    $this->consume(';');
    $this->Expr();
    $this->consume(';');
    // 0 ou 1 Expr
    if($this->isExpr()){ $this->Expr(); }
    $this->consume(')');
    $this->Stmt();
  }

  private function ReturnStmt() {
    $this->consume('return');
    // 0 ou 1 Expr
    if($this->isExpr()){ $this->Expr(); }
  }

  private function BreakStmt() {
    $this->consume('break');
  }

  private function PrinsStmt(){
    $this->consume('print');
    $this->consume('(');
    // 0 ou N Expr
    $out = false;
    while(!$out){
      if($this->isExpr()){
        $this->Expr();
      }
      else{
        $out = true;
      }
    }
    $this->consume(')');
  }

  private function Expr(){
    switch($this->tok){
      case 'Constant': {
        $this->consume('Constant');
        $this->Expr1();
        break;
      }
      case 'this': {
        $this->consume('this');
        $this->Expr1();
        break;
      }
      case 'Call': {
        $this->consume('Call');
        $this->Expr1();
        break;
      }
      case '(': {
        $this->consume('(');
        $this->Expr();
        $this->consume(')');
        $this->Expr1();
        break;
      }
      case '-': {
        $this->consume('-');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '!': {
        $this->consume('!');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case 'ReadInteger': {
        $this->consume('ReadInteger');
        $this->consume('(');
        $this->consume(')');
        $this->Expr1();
        break;
      }
      case 'ReadLine': {
        $this->consume('ReadLine');
        $this->consume('(');
        $this->consume(')');
        $this->Expr1();
        break;
      }
      case 'new': {
        $this->consume('ID');
        $this->Expr1();
        break;
      }
      case 'NewArray': {
        $this->consume('NewArray');
        $this->consume('(');
        $this->Expr();
        $this->Type();
        $this->consume(')');
        $this->Expr1();
        break;
      }
      default: {
        if($this->isLValue()){
          $this->LValue();
          $this->F1();
        }
        else{
          $this->error();
        }
        break;
      }
    }
  }

  private function F1(){
    if($this->tok == '='){
      $this->consume('=');
      $this->Expr();
      $this->Expr1();
    }
    else{
      $this->Expr1();
    }
  }

  private function Expr1(){
    switch ($this->tok) {
      case '+':{
        $this->consume('+');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '-':{
        $this->consume('-');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '*':{
        $this->consume('*');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '/':{
        $this->consume('/');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '%':{
        $this->consume('%');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '<':{
        $this->consume('<');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '<=':{
        $this->consume('<=');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '>':{
        $this->consume('>');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '>=':{
        $this->consume('>=');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '==':{
        $this->consume('==');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '!=':{
        $this->consume('!=');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '&&':{
        $this->consume('&&');
        $this->Expr();
        $this->Expr1();
        break;
      }
      case '||':{
        $this->consume('||');
        $this->Expr();
        $this->Expr1();
        break;
      }
      default:{
        $this->error();
        break;
      }
    }
  }

  private function LValue(){
    if($this->lexem == 'ID'){
      $this->consume('ID');
    }
    else{
      $this->Expr();
      $this->F2();
    }
  }

  private function F2(){
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
    if($this->lexem == 'ID'){
      $this->consume('ID');
      $this->consume('(');
      $this->Actuals();
      $this->consume(')');
    }
    else if ($this->token == '('){
      $this->Expr();
      $this->consume('.');
      $this->consume('ID');
    }
    else{
      $this->consume('(');
      $this->Actuals();
      $this->consume(')');
    }
  }

  private function Actuals(){
    if($this->isExpr){
      $this->Expr();
      $out = false;
      while(!$out) {
        if($this->tok == ','){
          $this->consume(',');
          $this->Expr();
        }
        else{
          $out = true;
        }
      }
    }
    // pode ser VAZIO então não da erro caso não seja Expr
  }

  private function Constant(){
    switch($this->tok){
      case 'intConstant': {
        $this->consume('intConstant');
        break;
      }
      case 'boolConstant': {
        $this->consume('boolConstant');
        break;
      }
      case 'doubleConstant': {
        $this->consume('doubleConstant');
        break;
      }
      case 'stringConstant': {
        $this->consume('stringConstant');
        break;
      }
      case 'null': {
        $this->consume('null');
        break;
      }
      default: {
        $this->error();
      }
    }
  }


  //  HELPERS
  private function isType() {
    return (
      $this->tok == 'int' ||
      $this->tok == 'double' ||
      $this->tok == 'bool' ||
      $this->tok == 'string' ||
      $this->lexem == 'ID'
    );
  }

  private function isFunctionDecl() {
    $is = false;
    if ($this->isType() || $this->tok == 'void'){
      $indice = $this->indice + 2; // Token 2 a frente;
      if(isset($this->tokens[$indice])){
        if($this->tokens[$indice] == '('){
          $is = true;
        }
      }
    }
    return $is;
  }

  private function isPrototype(){
    return $this->isFunctionDecl();
  }

  private function isClassDecl() {
    return $this->tok == 'class';
  }

  private function isVariableDecl(){
    return $this->isVariableDecl();
  }

  private function isVariable() {
    $is = false;
    if ($this->isType()){
      $indice = $this->indice + 2; // Token 2 a frente;
      if(isset($this->tokens[$indice])){
        if($this->tokens[$indice] != '('){
          $is = true;
        }
      }
    }
    return $is;
  }

  private function isInterfaceDecl() {
    return $this->tok == 'interface';
  }

  private function isIfStmt(){
    return $this->tok == 'if';
  }

  private function isWhileStmt(){
    return $this->tok == 'while';
  }

  private function isForStmt(){
    return $this->tok == 'for';
  }

  private function isReturnStmt(){
    return $this->tok == 'return';
  }

  private function isBreakStmt(){
    return $this->tok == 'break';
  }

  private function isStmtBlock(){
    return $this->token == '{'
  }

  private function isPrintStmt(){
    return $this->tok == 'print';
  }

  private function isStmt(){
    if(
      $this->isIfStmt() ||
      $this->isWhileStmt() ||
      $this->isForStmt() ||
      $this->isBreakStmt() ||
      $this->isReturnStmt() ||
      $this->isPrintStmt() ||
      $this->isExpr() || // Duvida na interrogacao Expr?
      $this->isStmtBlock()
    )
  }

  private function isExpr(){
    return (
      $this->tok == 'Constant' ||
      $this->tok == 'Call' ||
      $this->tok == 'this' ||
      $this->tok == '-' ||
      $this->tok == '!' ||
      $this->tok == 'ReadInteger' ||
      $this->tok == 'ReadLine' ||
      $this->tok == 'new' ||
      $this->tok == 'NewArray' ||
      $this->tok == '(' ||
      $this->lexem == 'ID'
    );
  }

  private function isLValue(){
    return($this->lexem == 'ID' || $this->isExpr));
  }


}
