<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Registry\Contracts;

/**
 * @template T of object
 */
interface Keyed_Object_Registry_Interface {
	/**
	 * @return class-string<T>
	 */
	public function get_type(): string;

	/**
	 * @param string $key The key to retrieve an object for.
	 *
	 * @phpstan-return T
	 */
	public function get( string $key ): object;

	public function has( string $key ): bool;

	/**
	 * @return list<string>
	 */
	public function keys(): array;

	/**
	 * @return array<string, object> Array of T (kept as object at runtime in PHP 7.4)
	 *
	 * @psalm-return array<string, T>
	 *
	 * @phpstan-return array<string, T>
	 */
	public function all(): array;
}
