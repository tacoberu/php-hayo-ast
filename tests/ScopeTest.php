<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;


class ScopeTest extends TestCase
{

	function testScalar()
	{
		$inst = new Scope(['a' => Scalar::Int_(42)],
			Expr::Func_('inc', ['a'])
			);
		$this->assertSame("{a = 42 : Int; inc a}", (string) $inst);
	}

}
