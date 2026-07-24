<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Data;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

final class Attachment_Data implements Array_Convertible_Interface {
	/**
	 * ID of the attachment. Required.
	 */
	private int $id;
	private string $title;
	private string $mime_type;
	private string $thumbnail_url;

	public function __construct( int $id, string $title, string $mime_type, string $thumbnail_url ) {
		$this->id            = $id;
		$this->title         = $title;
		$this->mime_type     = $mime_type;
		$this->thumbnail_url = $thumbnail_url;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'id'            => $this->id,
			'mime_type'     => $this->mime_type,
			'thumbnail_url' => $this->thumbnail_url,
			'title'         => $this->title,
		];
	}
}
