<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;


class LambdaTest extends TestCase
{

	function testScalar()
	{
		$inst = new Lambda(['a'], Expr::Func_('inc', [Scalar::Int_(42)]));
		$this->assertSame("(a) -> inc 42 : Int", (string) $inst);
		$this->assertSame(['inc', 'a'], $inst->refs());
	}



	function testSymbol()
	{
		$inst = new Lambda(['a'], Expr::Func_('inc', ['a']));
		$this->assertSame("(a) -> inc a", (string) $inst);
		$this->assertSame(['inc', 'a'], $inst->refs());
	}



	function testComposite()
	{
		$inst = new Lambda(['a'], Composite::List_(['a', Scalar::Int_(42)]));
		$this->assertSame("(a) -> [a, 42 : Int]", (string) $inst);
		$this->assertSame(['a'], $inst->refs());
	}



	function testLambda()
	{
		$inst = new Lambda(['a'], new Lambda(['b'], Composite::List_(['a', 'b', Scalar::Int_(42)])));
		$this->assertSame("(a) -> (b) -> [a, b, 42 : Int]", (string) $inst);
		$this->assertSame(['b', 'a'], $inst->refs());
	}



	function testSymbolWithScope()
	{
		$inst = new Lambda(['a'], new Scope(['b' => Scalar::Int_(1),
			],
			Expr::Bin_('a', '+', 'b')
			));
		$this->assertSame("(a) -> {b = 1 : Int; a + b}", (string) $inst);
		$this->assertSame(['+', 'a'], $inst->refs());
	}



	function testCallBuildin()
	{
		$inst = new Scope(['b' => Scalar::Int_(1),
			],
			Expr::Func_('list.map', ['+', 'b'])
			);
		$this->assertSame("{b = 1 : Int; list.map + b}", (string) $inst);
		$this->assertSame(['list.map', '+'], $inst->refs());
	}



	function testCallLambda()
	{
		$inst = new Scope(['b' => Scalar::Int_(1),
			],
			Expr::Func_('list.map', [new Lambda(['a'], Expr::Bin_('a', '*', Scalar::Int_(42))), 'b'])
			);
		$this->assertSame("{b = 1 : Int; list.map (a) -> a * 42 : Int b}", (string) $inst);
		$this->assertSame(['list.map', '*'], $inst->refs());
	}



	/**
	 * Issue F2: `it.quantity` is a single, bareword-dotted-path IDENTIFIER
	 * token (the lexer glues the dot in, see HayoLexer::IDENTIFIER) — never
	 * its own AST node. As a plain string ref, an exact `in_array` match
	 * against the lambda's own args ('it') misses it; the path's root
	 * ('it') needs to be checked instead, or it leaks out as a spurious
	 * free variable.
	 */
	function testPathRootedInOwnArgIsNotAFreeVariable()
	{
		// Path is the whole body: no operator to hide it behind — this
		// direction already worked before the fix (see below), but is
		// pinned here too.
		$inst = new Lambda(['it'], 'it.quantity');
		$this->assertSame(['it'], $inst->refs());
	}



	function testPathRootedInOwnArgInsideBinaryExprIsNotAFreeVariable()
	{
		// `(acc it -> acc + it.quantity)` — this is the exact shape that
		// leaked: `it.quantity` as an *operand* of a binary expression.
		$inst = new Lambda(['acc', 'it'], Expr::Bin_('acc', '+', 'it.quantity'));
		$this->assertSame("(acc it) -> acc + it.quantity", (string) $inst);
		$this->assertSame(['+', 'acc', 'it'], $inst->refs());
	}



	function testPathRootedInArgOfNestedLambdaDoesNotLeakToContainingExpr()
	{
		// `list.fold xs 0 (acc it -> acc + it.quantity)` — this is how the
		// leak actually surfaced: Expr::refs() computes the refs a nested
		// Lambda contributes to its *containing* expression as
		// `array_diff($lambda->refs(), $lambda->getArgs())`. Before the
		// fix, $lambda->refs() itself already (wrongly) contained
		// 'it.quantity', which survives that diff against ['acc', 'it']
		// verbatim and leaks all the way up to the enclosing lambda
		// (`xs -> {...}` in the original bug) as an extra required bind.
		$inst = Expr::Func_('list.fold', [
			'xs',
			Scalar::Int_(0),
			new Lambda(['acc', 'it'], Expr::Bin_('acc', '+', 'it.quantity')),
			]);
		$this->assertSame(['list.fold', 'xs', '+'], $inst->refs());
	}

}
