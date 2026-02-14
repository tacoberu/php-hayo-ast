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
	}



	function testSymbol()
	{
		$inst = new Lambda(['a'], Expr::Func_('inc', ['a']));
		$this->assertSame("(a) -> inc a", (string) $inst);
	}



	function testComposite()
	{
		$inst = new Lambda(['a'], Composite::List_(['a', Scalar::Int_(42)]));
		$this->assertSame("(a) -> [a, 42 : Int]", (string) $inst);
	}



	function testLambda()
	{
		$inst = new Lambda(['a'], new Lambda(['b'], Composite::List_(['a', 'b', Scalar::Int_(42)])));
		$this->assertSame("(a) -> (b) -> [a, b, 42 : Int]", (string) $inst);
	}



	function testSymbolWithScope()
	{
		$inst = new Lambda(['a'], new Scope(['b' => Scalar::Int_(1),
			],
			Expr::Bin_('a', '+', 'b')
			));
		$this->assertSame("(a) -> {b = 1 : Int; a + b}", (string) $inst);
	}

}
