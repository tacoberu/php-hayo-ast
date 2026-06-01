<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use InvalidArgumentException;


/**
 * Mějme zápis:
 * ```
 * a = 1
 * a + a
 * ```
 * Výraz `a + a` je Expr. Ale celé je to ještě ve scope, které umožnuje vázání
 * symbolů. Součástí Expr vázání symbolů být nemůže, protože Expr může být na místech
 * kde by to nedávalo smysl. Například:
 * `print (a = 1; a)`
 *
 * Vázat na symbol mohu libovolnou hodnotu stejně jako jiný symbol.
 *
 * Scope se skládá z kolekce navázaných symbolů, a jednoho výrazu. Výraz může být
 * - Composite: `{a = 1; [1, a]}`
 * - Expr: `{a = 1; a + a}`
 * - Lambda: `{a = 1; (b) => a + b}`
 * - symbol: `{a = 1; a}`
 *
 * Nemůže být Scalar, Scope. Vracet scalar nedává smysl, proč potom vytvářím ty lokální hodnoty, a vracet
 * Scope mi nedává smysl, není first-class objekt. K tomu se hodí spíše lambda.
 */
class Scope implements Value, HasRefs
{

	/**
	 * @var array<string, Value | string>
	 */
	private array $lets;

	/**
	 * @var Expr | string
	 */
	private $expr;

	/**
	 * @param array<string, Value | string> $lets
	 * @param Expr | string $expr Výraz jako nevyhodnocený string vznikne v případě: `a = 1;a`
	 */
	function __construct(array $lets, $expr)
	{
		self::assertExprOfScope($expr);
		self::assertLetsIsExpected($lets);
		$this->lets = $lets;
		$this->expr = $expr;
	}



	/**
	 * @return array<string, Value | string>
	 */
	function getLets(): array
	{
		return $this->lets;
	}



	/**
	 * @return Value | string | null
	 */
	function lookupSymbol(string $m)
	{
		if (!isset($this->lets[$m])) {
			return Null;
		}
		return $this->lets[$m];
	}



	/**
	 * @return Expr | string
	 */
	function getExpr()
	{
		return $this->expr;
	}



	/**
	 * Vrátí všechny symboly, které jsou vyžadovány, a které nejsou obsaženy v $lets
	 * Takže ty v Expr ano.
	 * Symboly z $lets sice ne, ale tyto symboly mohou mít samy o sobě závislosti, a ty ano.
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];

		if ( ! is_string($this->expr)) {
			foreach ($this->expr->refs() as $x) {
				if (isset($this->lets[$x])) {
					continue;
				}
				$xs[] = $x;
			}
		}

		foreach ($this->lets as $term) {
			if ($term instanceof HasRefs) {
				foreach ($term->refs() as $x) {
					if (isset($this->lets[$x])) {
						continue;
					}
					$xs[] = $x;
				}
			}
		}

		return $xs;
	}



	function type(): string
	{
		return is_string($this->expr)
			? '?'
			: $this->expr->type();
	}



	/**
	 * `a = 1;a * a`
	 * `a = 1;[a, a]`
	 * Výraz jako nevyhodnocený string vznikne v případě: `a = 1;a`
	 * @param mixed $src
	 */
	private static function assertExprOfScope($src): void
	{
		if (is_string($src) || $src instanceof Expr || $src instanceof Composite || $src instanceof Form) {
			return;
		}
		throw new InvalidArgumentException("Support Expr | string: " . print_r($src, True));
	}



	/**
	 * @param array<string, Value | string> $xs
	 */
	private static function assertLetsIsExpected(array $xs): void
	{
		if (empty($xs)) {
			throw new InvalidArgumentException("Empty binds.");
		}
	}



	function __toString(): string
	{
		$xs = [];
		foreach ($this->lets as $name => $term) {
			$xs[] = "{$name} = {$term}";
		}
		$xs[] = (string) $this->expr;
		return '{' . implode("; ", $xs) . '}';
	}

}
