<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;


class ExprTest extends TestCase
{

	/**
	 * @param array<string> $expectedRefs
	 */
	#[DataProvider("dataScalar")]
	function testScalar(Expr $token, string $expectedPrint, array $expectedRefs)
	{
		$this->assertSame($expectedPrint, (string) $token);
		//~ dump($token->toCode());
		$this->assertSame($expectedRefs, $token->refs());
	}



	static function dataScalar()
	{
		return [
			'str.len "Lorem"' => [
				Expr::Func_("str.len", [Scalar::Str_("Lorem")]),
				"str.len 'Lorem' : Str",
				['str.len'],
			],
			"42 + 1" => [
				Expr::Bin_(Scalar::Int_(42), "+", Scalar::Int_(1)),
				"42 : Int + 1 : Int",
				['+'],
			],
			"a + 1" => [
				Expr::Bin_('a', "+", Scalar::Int_(1)),
				"a + 1 : Int",
				['a', '+'],
			],
		];
	}

}
