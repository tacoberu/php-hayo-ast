<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

/**
 * Závisí na nějakých symbolech, které se nám nepodařilo získat. Například
 * Očekávané chování u Expr, Lamgda, Scope.
 */
interface HasRefs
{

	/**
	 * @return list<string>
	 */
	function refs(): array;

}



interface Value
{

	function type(): string;



	function __toString(): string;

}
