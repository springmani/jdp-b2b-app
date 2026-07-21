<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent\Data;

use WPE\AITK\WP\Context\Page_Context;

final class Cookie_Consent_Config {
	private string $ajax_action;
	private string $plugin_page_view;
	private Page_Context $page_context;
	private string $user_capability;
	private string $notices_action;

	public function __construct(
		string $ajax_action,
		string $plugin_page_view,
		Page_Context $page_context,
		string $user_capability,
		string $notices_action
	) {
		$this->ajax_action      = $ajax_action;
		$this->plugin_page_view = $plugin_page_view;
		$this->page_context     = $page_context;
		$this->user_capability  = $user_capability;
		$this->notices_action   = $notices_action;
	}

	public function get_ajax_action(): string {
		return $this->ajax_action;
	}

	public function get_plugin_page_view(): string {
		return $this->plugin_page_view;
	}

	public function get_page_context(): Page_Context {
		return $this->page_context;
	}

	public function get_user_capability(): string {
		return $this->user_capability;
	}

	public function get_notices_action(): string {
		return $this->notices_action;
	}
}
