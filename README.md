# Hayo AST

Abstract Syntax Tree (AST) implementation for the Hayo scripting language in PHP.

> **Note:** This project is not useful on its own as it is a component of the [php-hayo](https://github.com/tacoberu/php-hayo) project.

## Installation

Install via Composer:

```bash
composer require tacoberu/hayo-ast
```

## Basic Usage

The library provides classes for representing various language constructs like expressions, scalars, lambdas, and scopes.

### Scalars

Represent basic values:

```php
use Taco\Hayo\Scalar;

$int = Scalar::Int_(42);
$str = Scalar::Str_("Hello World");
$bool = Scalar::Bool_(true);

echo (string) $int; // 42 : Int
```

### Expressions

Expressions can be functional calls or binary operations:

```php
use Taco\Hayo\Expr;
use Taco\Hayo\Scalar;

// Function call: str.len "Lorem"
$expr = Expr::Func_("str.len", [Scalar::Str_("Lorem")]);

// Binary operation: 1 + 2
$binExpr = Expr::Bin_(Scalar::Int_(1), "+", Scalar::Int_(2));
```

### References

You can track required symbols (references) in an expression:

```php
$expr = Expr::Bin_('a', "+", Scalar::Int_(1));
print_r($expr->refs()); // ['a', '+']
```

## License

This project is licensed under the MIT License.
