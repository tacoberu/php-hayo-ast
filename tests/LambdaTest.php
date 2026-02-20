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

}
