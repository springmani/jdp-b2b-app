<?php

namespace Wpe_Content_Engine\Helper\Search;

use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

/**
 * Abstract base class for resolvers that read search-related settings
 * from WordPress options and format them for the search backend.
 */
abstract class Abstract_Search_Option_Resolver {
	/**
	 * Debug logger instance.
	 *
	 * @var \Wpe_Content_Engine\Helper\Logging\Debug_Logger
	 */
	protected Debug_Logger $logger;

	/**
	 * Get the WordPress option name to read from.
	 *
	 * @return string
	 */
	abstract protected function get_option_name(): string;

	/**
	 * Get the error message prefix used when logging exceptions.
	 *
	 * @return string
	 */
	abstract protected function get_error_message_prefix(): string;

	/**
	 * @param array $option_value The non-empty option value from WordPress.
	 *
	 * @return array
	 *
	 * @throws \AtlasSearch\Hooks\InvalidIdPrefixError Exception.
	 */
	abstract protected function resolve( array $option_value ): array;

	/**
	 * Initialize the resolver.
	 *
	 * @param \Wpe_Content_Engine\Helper\Logging\Debug_Logger $logger Debug logger for error handling.
	 */
	public function __construct( Debug_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Read the WordPress option, validate it, and delegate to resolve().
	 *
	 * Handles empty/invalid option values and catches exceptions
	 * with consistent error logging.
	 *
	 * @return array The resolved result, or empty array on failure.
	 */
	public function resolve_option(): array {
		$option_value = get_option( $this->get_option_name(), [] );

		if ( empty( $option_value ) || ! is_array( $option_value ) ) {
			return [];
		}

		try {
			return $this->resolve( $option_value );
		} catch ( \Throwable $e ) {
			$message = $this->get_error_message_prefix() . $e->getMessage();

			$this->logger->log( $message );

			if ( function_exists( 'graphql_debug' ) ) {
				graphql_debug(
					$message,
					[
						'version' => WPE_SMART_SEARCH_VERSION,
						'type'    => 'WPE_SMART_SEARCH_DEBUG',
					]
				);
			}

			do_action( 'qm/info', $message );

			return [];
		}
	}

	/**
	 * Format a single document ID with optional prefix.
	 *
	 * @param string      $post_type The post type.
	 * @param int|string  $post_id   The post ID.
	 * @param string|bool $id_prefix The ID prefix, or false if none.
	 *
	 * @return string Formatted document ID: "post_type:post_id" or "prefix:post_type:post_id".
	 */
	protected function format_document_id( string $post_type, $post_id, $id_prefix ): string {
		return $id_prefix ? "{$id_prefix}:{$post_type}:{$post_id}" : "{$post_type}:{$post_id}";
	}
}
