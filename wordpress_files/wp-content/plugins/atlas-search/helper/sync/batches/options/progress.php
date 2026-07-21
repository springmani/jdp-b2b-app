<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches\Options;

class Progress {
	/**
	 * @var int;
	 */
	private $total_items;

	/**
	 * @var int
	 */
	private $synced_items;

	/**
	 * Progress constructor.
	 *
	 * @param int $total_items Total number of items to be synced.
	 * @param int $synced_items Already synced items.
	 */
	public function __construct( int $total_items, int $synced_items ) {
		$this->total_items  = $total_items;
		$this->synced_items = $synced_items;
	}

	public function get_total_items(): int {
		return $this->total_items;
	}

	public function get_synced_items(): int {
		return $this->synced_items;
	}

	/**
	 * @param int $items_number Increase synced items numbers.
	 */
	public function increase_synced_items( int $items_number ): void {
		$this->synced_items += $items_number;
	}

	public function get_rounded_percentage(): int {
		if ( 0 === $this->get_total_items() ) {
			return 0;
		}

		return (int) ( $this->synced_items / $this->total_items * 100 );
	}
}
