<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;


class ScalarTest extends TestCase
{

	#[DataProvider('dataScalar')]
	function testScalar(Scalar $token, string $expected)
	{
		$this->assertSame($expected, (string) $token);
		//~ dump($ast->toCode());
	}



	static function dataScalar()
	{
		return [
			"42" => [ new Scalar(42, Scalar::TypeInt),
				"42 : Int",
				],
			"0" => [ new Scalar(0, Scalar::TypeInt),
				"0 : Int",
				],

			"text" => [ new Scalar("text", Scalar::TypeStr),
				"'text' : Str",
				],
		];
	}

}
