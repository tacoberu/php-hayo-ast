<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;


class ScopeTest extends TestCase
{

	/**
	 * @param array<string> $expectedRefs
	 */
	#[DataProvider("dataScalar")]
	function testScalar(Scope $token, string $expectedPrint, array $expectedRefs)
	{
		$this->assertSame($expectedPrint, (string) $token);
		$this->assertSame($expectedRefs, $token->refs());
	}



	static function dataScalar()
	{
		return [
			'a = 42; inc a' => [
				new Scope(['a' => Scalar::Int_(42)],
					Expr::Func_('inc', ['a'])
					),
				"{a = 42 : Int; inc a}",
				['inc'],
			],
		];
	}

}
