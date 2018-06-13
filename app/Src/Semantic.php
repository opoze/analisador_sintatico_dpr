<?php

namespace App\Src;


// Progam          -> Decl+
// Decl            -> VariableDecl | FunctionDecl | ClassDecl | InterfaceDecl
// VariableDecl    -> Variable;
// Variable        -> Type ident
// Type            -> int Type1 | double Type1 | bool Type1 | string Type1 | ident Type1
// Type1           -> [] Type1
// FunctionDecla   -> Type ident ( Formals ) StmtBlock | void ident ( Formals ) StmtBlock
// Formals         -> Variable (, Variable)* | &
// ClassDecl       -> class ident (extentend ident)? (implements ident (,ident)*)* { Field* }
// Field           -> VariableDecl | FunctionDecl
// InterfaceDecl   -> interface ident  { Prototype* }
// Prototype       -> Type ident (Formals) ; | void ident (Formals) ;
// StmtBlock       -> { VariableDecl* Stmt* }
// Stmt            -> Expr? | IfStmt | WhileStmt | ForStmt | BreakStmt | ReturnStmt | PrintStmt | StmtBlock
// IfStmt          -> if (Expr) Stmt (else Stmt)*
// WhileStmt       -> while (Expr) StmtBlock
// ForStmt         -> for (Expr?; Expr; Expr?) Stmt
// ReturnStmt      -> return Expr?
// BreakStmt       -> break
// PrintStmt       -> print(Expr*)
// Expr            -> LValue F1 | Constant Expr1 | this Expr1 | Call Expr1 | (Expr) Expr1 | -Expr Expr1 | !Expr Expr1 | ReadInteger() Expr1 | ReadLine() Expr1 | new ident Expr1 | NewArray (Expr, Type) Expr1
// F1              -> = Expr Expr1 | Expr1
// Expr1           -> + Expr  Expr1 | - Expr  Expr1 | * Expr  Expr1 | / Expr  Expr1 | % Expr  Expr1 | < Expr  Expr1 |  <= Expr  Expr1 | > Expr  Expr1 | >= Expr  Expr1 | == Expr  Expr1 | != Expr  Expr1 | && Expr Expr1 | || Expr Expr1
// LValue          -> ident | Expr F2 | Expr F2
// F2              -> .ident | [Expr]
// Call            -> ident ( Actuals ) | Expr .ident | ( Actuals )
// Actuals         -> Expr (, Expr )* | &
// Constant        -> IntConstant | doubleConstant | boolConstatnt | stringConstant | null


use Exception;

class Semantic
{
  // Variável global que armazena o token lido
  private $tok = '';

  // Obtém o próximo token do analizador léxico
  private advance() {
    $tok = $this->getToken();
  }

  private getToken(){
    return '';
  }

  private consume(int t){
    if (tok == t){
      advance();
    }
    else{
      error();
    }
  }

}
