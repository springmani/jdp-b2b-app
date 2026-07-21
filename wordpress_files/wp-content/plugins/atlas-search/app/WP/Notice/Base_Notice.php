<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice;

use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;
use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;
use WPE\AITK\Core\DTO\Contracts\Identifiable_Interface;
use WPE\AITK\WP\Notice\Concerns\With_Unique_ID;
use WPE\AITK\WP\Notice\Contracts\Notice_Interface;

abstract class Base_Notice implements
	Notice_Interface,
	Array_Convertible_Interface,
	Array_Constructible_Interface,
	Identifiable_Interface {
	use With_Unique_ID;

	/**
	 * The message to be displayed in the notice.
	 *
	 * @var string
	 */
	private string $message;

	/**
	 * The type of notice. Common types include 'success', 'error', 'warning', and 'info'.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Whether the user can dismiss the notice.
	 *
	 * @var bool
	 */
	private bool $dismissible;

	/**
	 * ID attribute for the notice element.
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * Additional CSS classes to apply to the notice element.
	 *
	 * @var array<string>
	 */
	private array $additional_classes;

	/**
	 * Additional HTML attributes for the notice element.
	 *
	 * @var array<string, scalar>
	 */
	private array $attributes;

	/**
	 * @param array<string>         $additional_classes
	 * @param array<string, scalar> $attributes
	 */
	public function __construct(
		string $message,
		string $type,
		bool $dismissible = false,
		string $id = '',
		array $additional_classes = [],
		array $attributes = []
	) {
		$this->message            = $message;
		$this->type               = $type;
		$this->dismissible        = $dismissible;
		$this->id                 = $id;
		$this->additional_classes = $additional_classes;
		$this->attributes         = $attributes;
	}

	public function get_message(): string {
		return $this->message;
	}

	public function get_type(): string {
		return $this->type;
	}

	public function get_dismissible(): bool {
		return $this->dismissible;
	}

	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @return array<string>
	 */
	public function get_additional_classes(): array {
		return $this->additional_classes;
	}

	/**
	 * @inheritDoc
	 */
	public function get_attributes(): array {
		return $this->attributes;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'additional_classes' => $this->additional_classes,
			'attributes'         => $this->attributes,
			'dismissible'        => $this->dismissible,
			'id'                 => $this->id,
			'message'            => $this->message,
			'type'               => $this->type,
		];
	}
}
