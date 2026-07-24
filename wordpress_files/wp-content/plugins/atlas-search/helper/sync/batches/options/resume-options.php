<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches\Options;

class Resume_Options {
	/**
	 * @var string;
	 */
	private $entity;

	/**
	 * @var int
	 */
	private $batch_size;

	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var string
	 */
	private $site_id;

	/**
	 * @var ?\Wpe_Content_Engine\Helper\Sync\Batches\Options\Progress $progress
	 */
	private $progress;

	/**
	 * Resume_Options constructor.
	 *
	 * @param string                                                        $entity Entity.
	 * @param int                                                           $batch_size Batch Size.
	 * @param int                                                           $page Page.
	 * @param string                                                        $site_id string.
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Options\Progress|null $progress Progress object.
	 */
	public function __construct(
		string $entity = '',
		int $batch_size = Batch_Options::DEFAULT_BATCH_SIZE,
		int $page = 1,
		string $site_id = '',
		?Progress $progress = null
	) {
		$this->entity     = $entity;
		$this->batch_size = $batch_size;
		$this->page       = $page;
		$this->progress   = $progress;
		$this->site_id    = $site_id;
	}

	public function get_entity(): string {
		return $this->entity;
	}

	public function get_batch_size(): int {
		return $this->batch_size;
	}

	public function get_page(): int {
		return $this->page;
	}

	public function get_site_id(): string {
		return $this->site_id;
	}

	public function get_progress(): ?Progress {
		return $this->progress;
	}
}
