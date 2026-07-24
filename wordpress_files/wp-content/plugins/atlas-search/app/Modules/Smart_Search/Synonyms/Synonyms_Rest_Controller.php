<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms;

use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Delete_Request;
use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Delete_Response;
use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Save_Request;
use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Save_Response;
use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonyms_List_Response;
use WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface;
use WPE\AITK\WP\Rest\Route_Config;
use WPE\AITK\WP\User\Capability_Checker;
use WP_REST_Server;

/**
 * Synonyms REST Controller.
 *
 * Provides CRUD endpoints for synonym rules.
 * Proxies all operations to the backend GraphQL API.
 */
final class Synonyms_Rest_Controller implements Rest_Controller_Interface {
	public const SYNONYMS = '/synonyms';

	private Synonyms_GraphQL_Handler $handler;

	public function __construct( Synonyms_GraphQL_Handler $handler ) {
		$this->handler = $handler;
	}

	/**
	 * @inheritDoc
	 */
	public function get_route_configs(): array {
		return [
			new Route_Config(
				self::SYNONYMS,
				WP_REST_Server::READABLE,
				[ $this, 'get_all' ],
				Capability_Checker::MANAGE_OPTIONS,
				Synonyms_List_Response::class
			),
			new Route_Config(
				self::SYNONYMS,
				WP_REST_Server::CREATABLE,
				[ $this, 'save' ],
				Capability_Checker::MANAGE_OPTIONS,
				Synonym_Save_Response::class,
				Synonym_Save_Request::class
			),
			new Route_Config(
				self::SYNONYMS,
				WP_REST_Server::DELETABLE,
				[ $this, 'delete' ],
				Capability_Checker::MANAGE_OPTIONS,
				Synonym_Delete_Response::class,
				Synonym_Delete_Request::class
			),
		];
	}

	/**
	 * Returns all synonym rules.
	 */
	public function get_all(): Synonyms_List_Response {
		return $this->handler->get_all();
	}

	/**
	 * Creates or updates a synonym rule.
	 *
	 * @param \WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Save_Request $input Request data.
	 */
	public function save( Synonym_Save_Request $input ): Synonym_Save_Response {
		return $this->handler->save(
			$input->get_synonyms(),
			$input->has_id() ? $input->get_id() : null
		);
	}

	/**
	 * Deletes a synonym rule.
	 *
	 * @param \WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Delete_Request $input Request data.
	 */
	public function delete( Synonym_Delete_Request $input ): Synonym_Delete_Response {
		return $this->handler->delete( $input->get_id() );
	}
}
