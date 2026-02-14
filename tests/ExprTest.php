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

	#[DataProvider("dataScalar")]
	function testScalar(Expr $token, string $expected)
	{
		$this->assertSame($expected, (string) $token);
		//~ dump($ast->toCode());
	}



	static function dataScalar()
	{
		return [
			'string.len "Lorem"' => [
				Expr::Func_("string.len", [Scalar::Str_("Lorem")]),
				"string.len 'Lorem' : Str",
			],
			"42 + 1" => [
				Expr::Bin_(Scalar::Int_(42), "+", Scalar::Int_(1)),
				"42 : Int + 1 : Int",
			],
		];
	}

}
