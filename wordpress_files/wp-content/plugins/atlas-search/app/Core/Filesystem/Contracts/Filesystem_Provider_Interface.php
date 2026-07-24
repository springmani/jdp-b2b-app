<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem\Contracts;

interface Filesystem_Provider_Interface {
	public function exists( string $path ): bool;

	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function get_contents( string $path ): string;

	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function put_contents( string $path, string $content ): void;

	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function size( string $path ): int;

	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function append( string $path, string $content ): void;

	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function replace_contents( string $path, callable $transformer ): void;

	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function delete( string $path ): void;

	public function is_file( string $path ): bool;
}
