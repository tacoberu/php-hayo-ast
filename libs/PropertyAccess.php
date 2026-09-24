<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use InvalidArgumentException;


/**
 * Přístup na pole (property) výsledku libovolné hodnoty, ne jen pojmenované
 * proměnné:
 * `(List.first xs Null).product`
 * `[1, 2, 3].length`
 * `{a: 1}.a`
 *
 * Na rozdíl od `x.foo`, což je jediný IDENTIFIER token s tečkou uvnitř (viz
 * `HayoLexer::IDENTIFIER`), který se dál řeší jako BindValue s tečkovaným
 * jménem (BindValue::isPath()) a vyhledá se podle jména `x` v běhovém
 * kontextu, tady base může být libovolná hodnota — výsledek závorkovaného
 * výrazu, seznamu, slovníku, `if`/`match` atd. Field access se tedy musí
 * provést až za běhu, na hotové hodnotě base.
 *
 * Řetězení `(f x).a.b` vzniká zanořením:
 * `PropertyAccess(PropertyAccess(base, 'a'), 'b')`.
 */
class PropertyAccess implements Value, HasRefs
{

	/**
	 * @var Value | string
	 */
	private $base;

	private string $field;

	/**
	 * @param Value | string $base
	 */
	function __construct($base, string $field)
	{
		self::assertBase($base);
		$this->base = $base;
		$this->field = $field;
	}



	/**
	 * @param Value | string $base
	 */
	static function Of_($base, string $field): self
	{
		return new self($base, $field);
	}



	/**
	 * @return Value | string
	 */
	function getBase()
	{
		return $this->base;
	}



	function getField(): string
	{
		return $this->field;
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
		if (is_string($this->base)) {
			return [$this->base];
		}
		if ($this->base instanceof HasRefs) {
			return $this->base->refs();
		}
		return [];
	}



	/**
	 * @param mixed $src
	 */
	private static function assertBase($src): void
	{
		if (is_string($src) || $src instanceof Value) {
			return;
		}
		throw new InvalidArgumentException("Support Value | string: " . print_r($src, True));
	}



	function __toString(): string
	{
		return "({$this->base}).{$this->field}";
	}

}
