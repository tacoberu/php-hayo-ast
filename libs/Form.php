<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use InvalidArgumentException;
use LogicException;


/**
 * `if <condition> then <expresion true> else <expresion false>`
 */
class Form implements Value, HasRefs
{

	private string $name;

	/**
	 * @var list<Value | string>
	 */
	private array $items;

	/**
	 * @param list<Value | string> $xs
	 */
	function __construct(string $name, array $xs)
	{
		if (empty($xs)) {
			throw new InvalidArgumentException("Empty definitions.");
		}

		$this->name = $name;
		$this->items = $xs;
	}



	/**
	 * @param list<mixed> $chains
	 * @param string | Value $otherwise
	 */
	static function IfThenElse_(array $chains, $otherwise): self
	{
		$chains[] = (object) [
			'cond' => Null,
			'expr' => $otherwise,
		];
		return new self('if-then-else', $chains);
	}



	/**
	 * match subject | Pattern binds -> expr | …
	 *
	 * items[0]   = subject (Value | string)
	 * items[1..] = arms (object{pattern: string, binds: list<string>, expr: Value|string})
	 *
	 * @param Value|string $subject
	 * @param list<object{pattern: string, binds: list<string>, expr: Value|string}> $arms
	 */
	static function Match_($subject, array $arms): self
	{
		return new self('match', array_merge([$subject], $arms));
	}



	function getName(): string
	{
		return $this->name;
	}



	function type(): string
	{
		throw new LogicException("Comming soon... (2026.02.15 01:13:27 CET)");
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array
	{
		if ($this->name === 'match') {
			return $this->refsForMatch();
		}

		$xs = [];
		foreach ($this->items as $row) {
			if (isset($row->cond)) {
				if (is_string($row->cond)) {
					$xs[] = $row->cond;
				}
				elseif (is_object($row->cond) && $row->cond instanceof HasRefs) {
					$xs = array_merge($xs, $row->cond->refs());
				}
			}
			if (isset($row->expr)) {
				if (is_string($row->expr)) {
					$xs[] = $row->expr;
				}
				elseif (is_object($row->expr) && $row->expr instanceof HasRefs) {
					$xs = array_merge($xs, $row->expr->refs());
				}
			}
		}
		return array_unique($xs); // @phpstan-ignore return.type
	}



	/**
	 * @return list<string>
	 */
	private function refsForMatch(): array
	{
		$xs = [];

		// items[0] = subject
		$subject = $this->items[0];
		if (is_string($subject)) {
			$xs[] = $subject;
		}
		elseif ($subject instanceof HasRefs) {
			$xs = array_merge($xs, $subject->refs());
		}

		// items[1..] = arms; bound variable names are NOT free references
		foreach (array_slice($this->items, 1) as $arm) {
			$expr = $arm->expr;
			if (is_string($expr)) {
				if ( ! in_array($expr, $arm->binds, True)) {
					$xs[] = $expr;
				}
			}
			elseif ($expr instanceof HasRefs) {
				foreach ($expr->refs() as $r) {
					if ( ! in_array($r, $arm->binds, True)) {
						$xs[] = $r;
					}
				}
			}
		}

		return array_values(array_unique($xs));
	}



	/**
	 * @return list<Value | string>
	 */
	function getItems(): array
	{
		return $this->items;
	}



	function __toString(): string
	{
		// @TODO Dodělat i střeva z $items
		return $this->name;
	}

}
