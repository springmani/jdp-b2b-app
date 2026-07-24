<?php

namespace Wpe_Content_Engine\Helper\API\Sync_Data;

use DateTime;
use WPE\AITK\Modules\Smart_Search\Sync\Data\Last_Sync_Data;
use WPE\AITK\Modules\Smart_Search\Sync\Sync_Subscriber;
use WP_REST_Controller;
use WP_REST_Request;
use Wpe_Content_Engine\Helper\Client_Interface;
use Wpe_Content_Engine\Helper\Constants\Sync_Response_Status as Status;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;
use Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Factory;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Batch_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Progress;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Sync_Lock_Manager;
use Wpe_Content_Engine\Settings_Interface;

use const AtlasSearch\Index\MANUAL_INDEX;
use const Wpe_Content_Engine\Helper\Notifications\WPE_SMART_SEARCH_INDEX_READY;

/**
 * Sync data controller allowing syncing data from sync button
 */
class Sync_Data_Controller extends WP_REST_Controller {
	private const DEFAULT_LOCK_ROLLING_TIMEOUT = 10;

	/**
	 * @var \Wpe_Content_Engine\Helper\Client_Interface $client
	 */
	protected $client;

	/**
	 * @var \Wpe_Content_Engine\Settings_Interface $settings
	 */
	protected $settings;

	private string $resource_name;

	private static $site_ids = null;

	public function __construct( Client_Interface $client, Settings_Interface $settings ) {
		$this->client        = $client;
		$this->settings      = $settings;
		$this->namespace     = 'wpengine-smart-search/v1';
		$this->resource_name = '/sync-data';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name,
			[
				[
					'methods'             => 'PUT',
					'callback'            => [
						$this,
						'sync_data',
					],
					'permission_callback' => [
						$this,
						'permission_callback',
					],
				],
				'schema' => [
					$this,
					'get_schema',
				],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->resource_name,
			[
				[
					'methods'             => 'DELETE',
					'callback'            => [
						$this,
						'delete_sync_data',
					],
					'permission_callback' => [
						$this,
						'permission_callback',
					],
				],
			]
		);
	}

	/**
	 * Returns the Smart Search sync data info.
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 *
	 * @return \Wpe_Content_Engine\Helper\API\Sync_Data\Response
	 *
	 * @throws \Exception|\ErrorException Thrown if there is an issue processing the sync.
	 */
	public function sync_data( WP_REST_Request $request ) {
		// Start output buffering.
		ob_start();

		try {
			// Validate the REST parameters.
			$json   = $request->get_json_params();
			$schema = $this->get_schema();
			$result = rest_validate_value_from_schema( $request->get_json_params(), $schema, 'Body' );

			$site_ids      = $this->get_site_ids( \AtlasSearch\Support\WordPress\NETWORK_ADMIN === $json['siteId'] );
			$batch_options = new Batch_Options(
				\AtlasSearch\Index\get_batch_size(),
				1,
				Batch_Sync_Factory::DATA_TO_SYNC,
				$site_ids
			);

			return $this->manage_sync_data( $json, $result, $batch_options );
		} catch ( \Throwable $e ) {
			// Handle exceptions and clean the buffer.
			ob_end_clean();

			return new \WP_REST_Response(
				[
					'status'  => 'error',
					'message' => $e->getMessage(),
				],
				500
			);
		} finally {
			// Clean the output buffer to discard any echoed data.
			ob_end_clean();
		}
	}

	/**
	 * Reset sync data progress.
	 *
	 * @param \WP_REST_Request                                               $request WP Rest request.
	 * @param null|\Wpe_Content_Engine\Helper\Sync\Batches\Sync_Lock_Manager $sync_lock_manager WP Sync Lock manager.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete_sync_data(WP_REST_Request $request, Sync_Lock_Manager $sync_lock_manager = null) {
		$sync_lock_manager = $sync_lock_manager ?? new Sync_Lock_Manager( self::DEFAULT_LOCK_ROLLING_TIMEOUT );
		$can_start         = $sync_lock_manager->can_start( new DateTime() );

		if ( ! $can_start ) {
			return new \WP_REST_Response(
				[
					'status'  => Status::ERROR,
					'message' => 'A data sync seems to already be active! Please wait for it to finish.',
				]
			);
		}

		try {
			\AtlasSearch\Index\delete_all( MANUAL_INDEX );
			\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );
			\AtlasSearch\Support\WordPress\update_option( WPE_SMART_SEARCH_INDEX_READY, false );

			if ( ! is_multisite() ) {
				\AtlasSearch\Support\WordPress\delete_option( Sync_Subscriber::OPTION );
			}
		} catch ( \Throwable $e ) {
			$logger = new Debug_Logger();

			$logger->log(
				"An error occurred while trying to delete all data. Error message: {$e->getMessage()} \n"
				. "Trace: {$e->getTraceAsString()} "
			);

			return new \WP_REST_Response(
				[
					'status'  => Status::ERROR,
					'message' => 'Delete Sync Data Error: ' . $e->getMessage(),
				]
			);
		}

		return new \WP_REST_Response(
			[
				'status'  => Status::COMPLETED,
				'message' => 'Indexed data were deleted successfully!',
			]
		);
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request The WP Rest request.
	 *
	 * @return bool
	 */
	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Schema of the REST Endpoints
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$properties = [
			'uuid'   => [ 'type' => 'string' ],
			'siteId' => [ 'type' => 'string' ],
		];

		return [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'sync-data',
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
		];
	}

	public function sanitize_uuid( $uuid ) {
		if ( preg_match( '/^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/', $uuid ) ) {
			return $uuid;
		}

		return '';
	}

	public function get_site_name( $site_id ) {
		if ( is_multisite() ) {
			return get_blog_details( $site_id )->blogname;
		}

		// get site name if not multisite enabled.
		return get_bloginfo( 'name' );
	}

	private function get_site_ids( $is_network_activated ) {
		if ( $is_network_activated && null === self::$site_ids ) {
			self::$site_ids = get_sites(
				[
					'fields' => 'ids',
					'number' => 0,
				]
			);
		}

		return $is_network_activated ? self::$site_ids : [ get_current_blog_id() ];
	}

	private function get_synced_types() {
		return array_keys( Batch_Sync_Factory::DATA_TO_SYNC );
	}

	private function manage_sync_data( $json, $result, Batch_Options $batch_options ) {
		$site_id      = $batch_options->get_current_site_id();
		$site_name    = $this->get_site_name( $site_id );
		$synced_types = $this->get_synced_types();

		if ( is_wp_error( $result ) ) {
			return new Response(
				Status::ERROR,
				100,
				$result->get_error_message(),
				null,
				$site_name,
				$synced_types[0],
				$synced_types
			);
		}

		/** @var \Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options|null $resume_options */
		$resume_options = \AtlasSearch\Support\WordPress\get_option(
			Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
			null
		);
		$all_site_ids   = $batch_options->get_site_ids();

		if ( ! empty( $resume_options ) && ( $resume_options instanceof Resume_Options ) ) {
			$batch_options->calculate_with_resume( $resume_options );
		}

		$progress = ! isset( $resume_options ) || ! $resume_options->get_progress() ? new Progress(
			$this->get_count_data_to_be_synced( $all_site_ids ),
			0
		) : $resume_options->get_progress();

		$logger            = new Debug_Logger();
		$sync_lock_manager = new Sync_Lock_Manager( self::DEFAULT_LOCK_ROLLING_TIMEOUT );
		$uuid              = $this->sanitize_uuid( $json['uuid'] );
		$logger->log( "Sync lock ID given: {$uuid}" );

		$moment         = new DateTime();
		$can_start      = $sync_lock_manager->can_start( $moment, $uuid );
		$can_start_text = $can_start ? 'yes' : 'no';
		$logger->log( "Init check, can start: {$can_start_text}, Uuid supplied: {$uuid}" );

		if ( ! $can_start ) {
			// log details of the lock in place for diagnosis.
			$last_status = $sync_lock_manager->get_status();
			$active_uuid = $last_status->get_uuid();
			$ids_equal   = $active_uuid === $uuid ? 'yes' : 'no';
			$logger->log( "UUIDs equal? {$ids_equal}" );
			$last_updated      = $last_status->get_last_updated();
			$last_updated_text = $last_updated->format( 'Y-m-d H:i:s' );
			$logger->log(
				"Lock active [{$active_uuid}], last updated {$last_updated_text}! Cannot start a new sync! Exiting..."
			);
			$uuid = null;

			return new Response(
				Status::ERROR,
				100,
				'A data sync is already in progress. You or another user may have begun this process. '
				. 'Please wait a few seconds and try again.',
				$uuid,
				$site_name,
				$synced_types[0],
				$synced_types
			);
		}

		$logger->log( 'No lock present, starting sync...' );

		try {
			$uuid = $sync_lock_manager->start( $moment, $uuid );
		} catch ( \Throwable $e ) {
			$logger->log( "Something went wrong. Error message: {$e->getMessage()}" );
			$message = 'Sync Error: ' . $e->getMessage();

			$this->record_sync_result( false );

			return new Response( Status::ERROR, 0, $message, $uuid, $site_name, $synced_types[0], $synced_types );
		}

		$logger->log( "Sync lock acquired. Sync lock ID: {$uuid}" );

		$current_data = $batch_options->get_current_class_to_be_synced();
		$site_id      = $batch_options->get_current_site_id();
		$site_name    = $this->get_site_name( $site_id );

		if (
			! empty( $resume_options )
			&& ( $resume_options instanceof Resume_Options )
			&& 'COMPLETED' === $resume_options->get_entity()
		) {
			\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );

			$this->release_sync_lock( $sync_lock_manager, $uuid, $logger );

			$logger->log( 'Returning a status of COMPLETED' );
			\AtlasSearch\Support\WordPress\update_option( WPE_SMART_SEARCH_INDEX_READY, true );

			$this->record_sync_result( true );

			return new Response( Status::COMPLETED, 100, '', $uuid, $site_name, null, $synced_types );
		}

		if ( ! empty( $current_data ) ) {
			$short_name = $current_data['short_name'];
			$obj        = Batch_Sync_Factory::build(
				$current_data['class'],
				\AtlasSearch\Support\WordPress\NETWORK_ADMIN === $json['siteId'],
				$site_id
			);
			$page       = $batch_options->get_page();
			$items      = $obj->get_items( $page, \AtlasSearch\Index\get_batch_size() );
		}

		if ( empty( $items ) ) {
			if ( $batch_options->is_last() ) {
				\AtlasSearch\Support\WordPress\update_option(
					Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
					new Resume_Options( 'COMPLETED' ),
					false
				);
				$logger->log( 'COMPLETING ....' );

				return new Response(
					Status::PENDING,
					100,
					"{$site_name}: Syncing {$short_name}",
					$uuid,
					$site_name,
					$short_name,
					$synced_types
				);
			}

			$debug_message = "Current Site id: {$site_id} - Post Type{$short_name}: page -> {$page}";

			if ( $batch_options->is_last_class_to_be_synced() ) {
				$site_id = $batch_options->get_next_site_id();
			}

			$next_short_name = $batch_options->get_next_class_name();

			\AtlasSearch\Support\WordPress\update_option(
				Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
				new Resume_Options( $next_short_name, \AtlasSearch\Index\get_batch_size(), 1, $site_id, $progress ),
				false
			);
			$logger->log(
				"Returning a status of PENDING with lockID [{$uuid}] for object {$short_name}, {$debug_message}."
			);

			return new Response(
				Status::PENDING,
				$progress->get_rounded_percentage(),
				"{$site_name}: Syncing {$short_name}",
				$uuid,
				$site_name,
				$short_name,
				$synced_types
			);
		}

		$logger->log( "Performing sync batch and incrementing page with lockID [{$uuid}] for object {$short_name}." );

		try {
			$obj->sync( $items );
			++$page;
			$progress->increase_synced_items( count( $items ) );
		} catch ( \Throwable $e ) {
			$message = $e->getMessage();
			$logger->log( "Something went wrong. Error message: {$message}" );

			$this->release_sync_lock( $sync_lock_manager, $uuid, $logger );
		} finally {
			\AtlasSearch\Support\WordPress\update_option(
				Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
				new Resume_Options(
					$short_name,
					\AtlasSearch\Index\get_batch_size(),
					$page,
					$batch_options->get_current_site_id(),
					$progress
				),
				false
			);

			if ( isset( $e ) && ( $e instanceof \Throwable ) ) {
				$logger->log( "Returning a status of ERROR with lockID [{$uuid}] for object {$short_name}." );
				$message = 'Sync Error: ' . $e->getMessage();

				$this->record_sync_result( false );

				return new Response( Status::ERROR, 0, $message, $uuid, $site_name, $short_name, $synced_types );
			}

			$logger->log( "Finally{} Returning a status of PENDING with lockID [{$uuid}] for object {$short_name}." );

			return new Response(
				Status::PENDING,
				$progress->get_rounded_percentage(),
				"{$site_name}: Syncing {$short_name}",
				$uuid,
				$site_name,
				$short_name,
				$synced_types
			);
		}
	}

	private function record_sync_result( bool $success ): void {
		if ( is_multisite() ) {
			return;
		}

		$last_sync = $success
			? Last_Sync_Data::create_success( ( new DateTime() )->format( 'c' ) )
			: Last_Sync_Data::create_failure( ( new DateTime() )->format( 'c' ) );

		\AtlasSearch\Support\WordPress\update_option( Sync_Subscriber::OPTION, $last_sync->to_array() );
	}

	/**
	 * Release sync lock.
	 *
	 * @param \Wpe_Content_Engine\Helper\Sync\Batches\Sync_Lock_Manager $sync_lock_manager Sync lock manager instance.
	 * @param string|null                                               $uuid UUID for the sync.
	 * @param \Wpe_Content_Engine\Helper\Logging\Debug_Logger           $logger Logger instance.
	 *
	 * @return void
	 */
	private function release_sync_lock( Sync_Lock_Manager $sync_lock_manager, ?string $uuid, Debug_Logger $logger ) {
		if ( ! empty( $uuid ) ) {
			$moment = new DateTime();
			$sync_lock_manager->finish( $moment, $uuid );
			$logger->log( "Sync lock ID {$uuid} released!" );
		} else {
			$logger->log( 'No sync lock acquired this run.!' );
		}
	}

	/**
	 * In case of multisite we provide total site ids to be synced.
	 *
	 * @param array $site_ids .
	 *
	 * @return int
	 */
	private function get_count_data_to_be_synced( $site_ids ) {
		$counter              = 0;
		$is_network_activated = count( $site_ids ) > 1;

		foreach ( $site_ids as $site_id ) {
			if ( $is_network_activated ) {
				switch_to_blog( $site_id );
			}

			foreach ( Batch_Sync_Factory::DATA_TO_SYNC as $item ) {
				$obj      = Batch_Sync_Factory::build( $item, $is_network_activated, $site_id );
				$counter += $obj->get_total_items();
			}

			if ( $is_network_activated ) {
				restore_current_blog();
			}
		}

		return $counter;
	}
}
