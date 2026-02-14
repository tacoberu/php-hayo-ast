<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use LogicException;


/**
 * Označuje složená z dalších hodnot (scalar nebo composite)
 * složených struktur (Composite).
 * - List/Array [1, 2, 3] - složený z více hodnot
 * - Dictionary/Map {a: 1, b: 2} - složený z párů
 * - Set {1, 2, 3} - kolekce unikátních hodnot
 * - Tuple (1, "text", true) - uspořádaná n-tice
 * - Objekty/struktury s fieldy
 */
class Composite implements Value, HasRefs
{

	const TypeList = 'List';
	const TypeDict = 'Dict';
	const TypeTuple = 'Tuple';

	/**
	 * @var array<mixed> | \stdClass
	 */
	private $items;

	private string $type;

	/**
	 * @param array<mixed> | \stdClass $items
	 */
	private function __construct($items, string $type)
	{
		$this->items = $items;
		$this->type = $type;
	}



	/**
	 * @param list<Value | symbol> $items
	 */
	static function List_(array $items): self
	{
		return new self($items, self::TypeList);
	}



	/**
	 * @param array<string, Value | symbol> | \stdClass $items
	 */
	static function Dict_($items): self
	{
		return new self((object)$items, self::TypeDict);
	}



	/**
	 * @param list<Value | symbol> $items
	 */
	static function Tuple_(array $items): self
	{
		return new self($items, self::TypeTuple);
	}



	function type(): string
	{
		return $this->type;
	}



	/**
	 * @return array<mixed> | \stdClass
	 */
	function getItems()
	{
		return $this->items;
	}



	/**
	 * @return list<string>
	 */
	function refs(): array
	{
		throw new LogicException("Comming soon... (2026.02.15 01:01:53 CET)");
	}



	function __toString(): string
	{
		switch ($this->type) {
			case self::TypeList:
				$xs = [];
				foreach ((array) $this->items as $v) {
					$xs[] = "{$v}";
				}
				return '[' . implode(', ', $xs) . ']';

			case self::TypeDict:
				$xs = [];
				foreach ((array) $this->items as $k => $v) {
					$xs[] = "{$k}: {$v}";
				}
				return '{' . implode(', ', $xs) . '}';

			case self::TypeTuple:
				$xs = [];
				foreach ((array) $this->items as $v) {
					$xs[] = "{$v}";
				}
				return '(' . implode(', ', $xs) . ')';

			default:
				throw new LogicException("Illegal type of composite: '{$this->type}'.");
		}
	}

}
