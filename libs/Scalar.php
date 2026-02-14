<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

/**
 * Označuje jednoduchou, atomickou, nedělitelnou hodnotu - na rozdíl od
 * složených struktur (Composite).
 * Základní typy:
 * - Čísla (integer, real)
 * - Text/string
 * - Boolean (true/false)
 * - Null/None/Unit
 * - Datum/čas
 */
class Scalar implements Value
{

	const TypeInt = 'Int';
	const TypeReal = 'Real';
	const TypeStr = 'Str';
	const TypeBool = 'Bool';
	const TypeNull = 'Null';
	const TypeSymbol = 'Symbol';

	/**
	 * @var mixed
	 */
	private $val;

	private string $type;

	/**
	 * @param int | float | string | bool | null $val
	 */
	function __construct($val, string $type)
	{
		$this->val = $val;
		$this->type = $type;
	}



	static function Int_(int $val): self
	{
		return new self($val, self::TypeInt);
	}



	static function Real_(float $val): self
	{
		return new self($val, self::TypeReal);
	}



	static function Str_(string $val): self
	{
		return new self($val, self::TypeStr);
	}



	static function Bool_(bool $val): self
	{
		return new self($val, self::TypeBool);
	}



	static function Null_(): self
	{
		return new self(Null, self::TypeNull);
	}



	static function Symbol_(string $val): self
	{
		return new self($val, self::TypeSymbol);
	}



	function type(): string
	{
		return $this->type;
	}



	/**
	 * @return mixed
	 */
	function getValue()
	{
		return $this->val;
	}



	function __toString(): string
	{
		switch (strtoupper($this->type)) {
			case 'STR':
				$val = var_export($this->val, True);
				return "{$val} : {$this->type}";

			default:
				return "{$this->val} : {$this->type}";
		}
	}

}
