<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches\Options;

class Batch_Options {
	/**
	 * @var string Batch Options Key
	 */
	public const OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME = 'wpe_content_engine_sync_resume';

	/**
	 * @var int
	 */
	public const DEFAULT_BATCH_SIZE = 10;

	/**
	 * @var int
	 */
	private $batch_size;

	/**
	 * @var int
	 */
	private $page;

	/**
	 * Data to be synced! Can change during the process, so we keep the initial data in all_data_to_be_synced.
	 *
	 * @var \Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Interface[] $data_to_be_synced
	 */
	private $data_to_be_synced;

	/**
	 * We keep the initial data to be synced in this variable.
	 *
	 * @var \Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Interface[] $data_to_be_synced
	 */
	private $all_data_to_be_synced;

	/**
	 * If we are syncing multiple sites, we keep the site ids in this variable.
	 *
	 * @var string[]
	 */
	private $site_ids;

	/**
	 * Batch_Options constructor.
	 *
	 * @param int                                                            $batch_size        Batch Size.
	 * @param int                                                            $page              Page.
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Interface[] $data_to_be_synced Data to be synced.
	 * @param array                                                          $site_ids          Site IDs.
	 */
	public function __construct( int $batch_size, int $page, array $data_to_be_synced, array $site_ids ) {
		$this->all_data_to_be_synced = $data_to_be_synced;
		$this->data_to_be_synced     = $this->all_data_to_be_synced;
		$this->batch_size            = $batch_size;
		$this->page                  = $page;
		$this->site_ids              = array_map( 'strval', $site_ids );
	}

	/**
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options $resume_options Resume options.
	 */
	public function calculate_with_resume( Resume_Options $resume_options ): void {
		$this->updated_sync_data( $resume_options );
		$this->update_page( $resume_options );
		$this->update_site_ids( $resume_options );
	}

	public function get_batch_size(): int {
		return $this->batch_size;
	}

	public function get_page(): int {
		return $this->page;
	}

	public function is_last_class_to_be_synced(): bool {
		return count( $this->data_to_be_synced ) <= 1;
	}

	public function is_last_site_id(): bool {
		return count( $this->site_ids ) <= 1;
	}

	public function get_current_class_to_be_synced(): array {
		if ( empty( $this->data_to_be_synced ) ) {
			return [];
		}

		$short_name = array_key_first( $this->data_to_be_synced );

		return [
			'short_name' => $short_name,
			'class'      => $this->data_to_be_synced[ $short_name ],
		];
	}

	public function get_current_site_id(): ?string {
		if ( empty( $this->site_ids ) ) {
			return null;
		}

		return $this->site_ids[ array_key_first( $this->site_ids ) ];
	}

	public function get_next_class_name(): ?string {
		if ( $this->is_last() ) {
			return null;
		}

		$is_last_class = $this->is_last_class_to_be_synced();

		return $is_last_class ? array_keys( $this->all_data_to_be_synced )[0] : array_keys( $this->data_to_be_synced )[1];
	}

	public function get_next_site_id(): ?string {
		if ( $this->is_last_site_id() ) {
			return null;
		}

		return $this->site_ids[1];
	}

	/**
	 * @return bool
	 */
	public function is_last() {
		return $this->is_last_class_to_be_synced() && $this->is_last_site_id();
	}

	/**
	 * @return array|string[]
	 */
	public function get_site_ids(): array {
		return $this->site_ids;
	}

	/**
	 * Update current page number based on the resume options.
	 *
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options $resume_options .
	 *
	 * @return void
	 */
	protected function updated_sync_data( Resume_Options $resume_options ) {
		$index = array_search( $resume_options->get_entity(), array_keys( $this->data_to_be_synced ) );

		if ( false !== $index ) {
			$this->data_to_be_synced = array_slice( $this->data_to_be_synced, $index );
		}
	}

	/**
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options $resume_options .
	 *
	 * @return void
	 */
	protected function update_page( Resume_Options $resume_options ) {
		$this->page = $this->get_batch_size() !== $resume_options->get_batch_size() ? floor(
			$resume_options->get_batch_size() * $resume_options->get_page() / $this->get_batch_size()
		) : $resume_options->get_page();
	}

	/**
	 * Updates site ids based on resume options.
	 *
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options $resume_options .
	 *
	 * @return void
	 */
	protected function update_site_ids( Resume_Options $resume_options ) {
		$site_id_index = array_search( $resume_options->get_site_id(), $this->site_ids );

		if ( false !== $site_id_index ) {
			$this->site_ids = array_slice( $this->site_ids, $site_id_index );
		}
	}
}
