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
