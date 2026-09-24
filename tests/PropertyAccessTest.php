<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;


class PropertyAccessTest extends TestCase
{

	function testBaseIsExpr()
	{
		$inst = PropertyAccess::Of_(
			Expr::Func_('List.first', ['xs', 'Null']),
			'product'
			);
		$this->assertSame('?', $inst->type());
		$this->assertEquals(Expr::Func_('List.first', ['xs', 'Null']), $inst->getBase());
		$this->assertSame('product', $inst->getField());
		$this->assertSame(['List.first', 'xs', 'Null'], $inst->refs());
		$this->assertSame('(List.first xs Null).product', (string) $inst);
	}



	function testBaseIsBareSymbol()
	{
		// `.pole` na obyčejném symbolu, ne na výrazu. Nikdy nevznikne parserem
		// (bareword `x.pole` se řeší jinak, viz PropertyAccess doc), ale je to
		// legální konstrukce AST.
		$inst = PropertyAccess::Of_('x', 'product');
		$this->assertSame(['x'], $inst->refs());
	}



	function testChaining()
	{
		// `(f x).a.b`
		$inst = PropertyAccess::Of_(
			PropertyAccess::Of_(Expr::Func_('f', ['x']), 'a'),
			'b'
			);
		$this->assertSame(['f', 'x'], $inst->refs());
		$this->assertSame('((f x).a).b', (string) $inst);
	}



	function testBaseWithoutRefs()
	{
		$inst = PropertyAccess::Of_(Scalar::Int_(42), 'x');
		$this->assertSame([], $inst->refs());
	}



	function testInvalidBase()
	{
		$this->expectException(InvalidArgumentException::class);
		new PropertyAccess(42, 'x');
	}

}
