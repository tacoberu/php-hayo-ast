<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use InvalidArgumentException;


/**
 * Výraz obsahující vlastní lokální definice a definující argumenty. Neobsahuje
 * jméno, protože to se týká přiřazení.
 * `(x) -> x + 41`
 * `() -> print 41`
 * Všimni si:
 * ```
 * a = 42;
 * inc = (a: Int): Int -> a + 1;
 * inc a
 * ```
 * Mám BlockExpr, který má navázané dva symboly. Lambda, která je navázaná
 * na symbol `inc`, nemůže být jen prosté Expr, protože si vynucuje argumenty.
 * Argumenty musím definovat explicitně, protože jinak by se mi na ten
 * symbol `a` navázal předchozí symbol `a = 42`.
 *
 * Jako tělo lambdy může figurovat:
 * - Expr: `(x) -> x + x`
 * - Scalar: `() -> 42`
 * - Composite: `(x) -> [x]`
 * - symbol: `(x) -> x`
 * - Lambda: `(x) -> (y) -> x + y`
 * - Scope: `(x) -> y = 55; x + y`
 */
class Lambda implements Value, HasRefs
{

	/**
	 * Lambda vyžaduje doplnit argumenty.
	 * @var list<string>
	 */
	private $args;

	/**
	 * Vlastní logika lambdy. String jako hodnota nastane v případě, když:
	 * `a = 1; a`
	 * @var Expr | string
	 */
	private $expr;

	/**
	 * @param list<string> $args
	 * @param Value | string $expr
	 */
	function __construct(array $args, $expr)
	{
		self::assertExprOfLambda($expr);
		//~ foreach ($args as $i => $x) {
			//~ Validators::assert($i, 'int');
			//~ Validators::assert($x, 'string:1..255');
		//~ }
		$this->args = $args;
		$this->expr = $expr;
	}



	function type(): string
	{
		return '?';
	}



	/**
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];
		if ($this->expr instanceof HasRefs) {
			foreach ($this->expr->refs() as $x) {
				if ( ! in_array($x, $this->args, True)) {
					$xs[] = $x;
				}
			}
		}
		foreach ($this->args as $x) {
			if (is_string($x)) { // @phpstan-ignore function.alreadyNarrowedType
				$xs[] = $x;
			}
			else {
				$xs = array_merge($xs, $x->refs());
			}
		}
		return $xs;
	}



	/**
	 * @return Expr | string
	 */
	function getExpr()
	{
		return $this->expr;
	}



	/**
	 * @return list<string>
	 */
	function getArgs(): array
	{
		return $this->args;
	}



	private static function assertExprOfLambda($src): void
	{
		if (is_string($src) || $src instanceof Value) {
			return;
		}
		throw new InvalidArgumentException("Support Value | string: " . print_r($src, True));
	}



	function __toString(): string
	{
		$args = [];
		foreach ($this->args as $x) {
			$args[] = (string) $x;
		}
		$args = implode(' ', $args);
		return "({$args}) -> {$this->expr}";
	}

}
