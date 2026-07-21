<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test\Data;

final class Test_Result {
	public const GOOD        = 'good';
	public const RECOMMENDED = 'recommended';
	public const CRITICAL    = 'critical';

	private string $label;
	private string $status;
	private string $description;
	private string $actions;
	private string $test;

	private function __construct( string $label, string $status, string $description, string $test, string $actions ) {
		$this->label       = $label;
		$this->status      = $status;
		$this->description = $description;
		$this->test        = $test;
		$this->actions     = $actions;
	}

	public static function good( string $label, string $description, string $test, string $actions = '' ): self {
		return new self( $label, self::GOOD, $description, $test, $actions );
	}

	public static function recommended( string $label, string $description, string $test, string $actions = '' ): self {
		return new self( $label, self::RECOMMENDED, $description, $test, $actions );
	}

	public static function critical( string $label, string $description, string $test, string $actions = '' ): self {
		return new self( $label, self::CRITICAL, $description, $test, $actions );
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_status(): string {
		return $this->status;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function get_actions(): string {
		return $this->actions;
	}

	public function get_test(): string {
		return $this->test;
	}
}
