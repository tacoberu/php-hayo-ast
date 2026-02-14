<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;


class ASTTest extends TestCase
{

	function testDevelp(): void
	{
		//~ $code = "42";
		$ast = new Scalar(42, "Int");
		//~ dump($ast);
		$this->assertSame("42 : Int", (string) $ast);
		//~ dump($ast->toCode());
	}

}
