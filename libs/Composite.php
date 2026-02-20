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
 * - List [1, 2, 3] - složený z více hodnot
 * - Map {a: 1, b: 2} - složený z párů, podle typu bud Dict, nebo Record
 * - Tuple (1, "text", true) - uspořádaná n-tice
 */
class Composite implements Value, HasRefs
{

	const TypeList = 'List';
	const TypeDict = 'Dict';
	const TypeRecord = 'Record';
	const TypeTuple = 'Tuple';

	/**
	 * @var array<mixed> | array<string, mixed>
	 */
	private array $items;

	private string $type;

	/**
	 * @param array<mixed> | \stdClass $items
	 */
	private function __construct($items, string $type)
	{
		// self::assertItems($items);
		$this->items = (array) $items;
		$this->type = $type;
	}



	/**
	 * @param list<Value | string> $items
	 */
	static function List_(array $items, string $type = self::TypeList): self
	{
		return new self($items, $type);
	}



	/**
	 * @param array<string, Value | string> | \stdClass $items
	 */
	static function Dict_($items, string $type = self::TypeDict): self
	{
		return new self($items, $type);
	}



	/**
	 * @param array<string, Value | string> | \stdClass $items
	 */
	static function Record_($items, string $type = self::TypeRecord): self
	{
		return new self($items, $type);
	}



	/**
	 * @param array<string, Value | string> | \stdClass $items
	 * @param string $type Aka `Dict<Str>`, `{name: Str, sex: Sex, age: Int}`.
	 */
	static function Map_($items, string $type): self
	{
		return new self((array) $items, $type);
	}



	/**
	 * @param list<Value | string> $items
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
		return $this->type === self::TypeDict
			? (object) $this->items
			: $this->items;
	}



	/**
	 * @return list<string>
	 */
	function refs(): array
	{
		if (empty($this->items)) {
			return [];
		}

		$xs = [];
		foreach ($this->items as $v) {
			if (is_string($v)) {
				$xs[] = $v;
			}
			elseif ($v instanceof HasRefs) {
				$xs = array_merge($xs, $v->refs());
			}
		}
		return array_unique($xs); // @phpstan-ignore return.type
	}



	function __toString(): string
	{
		switch ($this->type) {
			case self::TypeList:
				$xs = [];
				foreach ($this->items as $v) {
					$xs[] = "{$v}";
				}
				return '[' . implode(', ', $xs) . ']';

			case self::TypeDict:
				$xs = [];
				foreach ($this->items as $k => $v) {
					$xs[] = "{$k}: {$v}";
				}
				return '{' . implode(', ', $xs) . '}';

			case self::TypeTuple:
				$xs = [];
				foreach ($this->items as $v) {
					$xs[] = "{$v}";
				}
				return '(' . implode(', ', $xs) . ')';

			default:
				throw new LogicException("Illegal type of composite: '{$this->type}'.");
		}
	}

}
