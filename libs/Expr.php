<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use InvalidArgumentException;
use ArrayAccess;
use BadMethodCallException;
use ReturnTypeWillChange;
use LogicException;


/**
 * 1 + 2
 * 1 + m
 * n + x
 * print 1
 * print x
 * print (x + 1)
 * print (x + (1 + 1))
 *
 * Expression je tvořené závorkami, a může se zanořovat. Obsahuje požadavky na
 * symboly - viz refs(). Expr sám o sobě žádné vázané symboly nedefinuje, na to slouží
 * Scope. Expr je zároven volání funkce nebo operátoru - viz konstruktory Func_ a Bin_
 *
 * @implements ArrayAccess<int, Value | string>
 */
class Expr implements Value, ArrayAccess, HasRefs
{

	const NotationPrefix = 'prefix';
	const NotationInfix = 'infix';
	const NotationPostfix = 'postfix';

	/**
	 * @var list<Value | string>
	 */
	private array $items;

	private string $notation;

	/**
	 * @param list<Value | string> $xs
	 */
	function __construct(array $xs, string $notation)
	{
		if (empty($xs)) {
			throw new InvalidArgumentException("Empty definitions.");
		}

		foreach ($xs as $x) {
			self::assertExpr($x);
			$this->items[] = $x;
		}
		$this->notation = $notation;
	}



	/**
	 * @param string | Value $fn
	 * @param list<string | Value> $args
	 */
	static function Func_($fn, array $args): self
	{
		array_unshift($args, $fn);
		return new self($args, self::NotationPrefix);
	}



	/**
	 * @param string | Value $left
	 * @param string | Value $fn
	 * @param string | Value $right
	 */
	static function Bin_($left, $fn, $right): self
	{
		return new self([$left, $fn, $right], self::NotationInfix);
	}



	function getNotation(): string
	{
		return $this->notation;
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
		$items = $this->items;

		// func
		if ($items[0] instanceof BuildinFunc) {
			array_shift($items);
		}
		// operator
		elseif (isset($items[1]) && $items[1] instanceof BuildinFunc) {
			$fn1 = array_shift($items);
			array_shift($items);
			$items = array_merge([$fn1], $items);
		}

		$xs = [];
		foreach ($items as $x) {
			if (is_string($x)) {
				$xs[] = $x;
			}
			else if ($x instanceof HasRefs) {
				$xs = array_merge($xs, $x->refs());
			}
		}

		return array_unique($xs);
	}



	/**
	 * @return list<Value | string>
	 */
	function getItems(): array
	{
		return $this->items;
	}



	function offsetSet($offset, $value): void
	{
		throw new BadMethodCallException("Read-only");
	}



	function offsetExists($offset): bool
	{
		return isset($this->items[$offset]);
	}



	function offsetUnset($offset): void
	{
		throw new BadMethodCallException("Read-only");
	}



	#[ReturnTypeWillChange]
	function offsetGet($offset)
	{
		return $this->offsetExists($offset)
			? $this->items[$offset]
			: null;
	}



	/**
	 * @param mixed $m
	 */
	private static function assertExpr($m): void
	{
		if (is_string($m) && strpos($m, ' ')) {
			throw new InvalidArgumentException("Illegal format of symbol name: `$m'.");
		}
	}



	function __toString(): string
	{
		$xs = [];
		foreach ($this->items as $x) {
			$xs[] = $x instanceof self
				? "({$x})"
				: "{$x}";
		}
		return implode(' ', $xs);
	}

}
