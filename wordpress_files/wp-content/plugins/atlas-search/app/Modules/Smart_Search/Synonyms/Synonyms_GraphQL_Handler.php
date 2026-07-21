<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms;

use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Delete_Response;
use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonym_Save_Response;
use WPE\AITK\Modules\Smart_Search\Synonyms\Data\Synonyms_List_Response;
use WPE\AITK\Modules\Smart_Search\Synonyms\Operations\Delete_Synonym_Mutation;
use WPE\AITK\Modules\Smart_Search\Synonyms\Operations\Get_Synonyms_Query;
use WPE\AITK\Modules\Smart_Search\Synonyms\Operations\Save_Synonym_Mutation;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface;
use WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Exception;
use WPE\AITK\WP\Rest\Exceptions\Server_Exception;

/**
 * Synonyms GraphQL Handler.
 *
 * Constructs operations, executes them against the client,
 * and translates GraphQL exceptions into REST exceptions at the boundary.
 */
final class Synonyms_GraphQL_Handler {
	private GraphQL_Client_Interface $client;

	private Logger_Interface $logger;

	public function __construct( GraphQL_Client_Interface $client, Logger_Interface $logger ) {
		$this->client = $client;
		$this->logger = $logger;
	}

	/**
	 * @throws \WPE\AITK\WP\Rest\Exceptions\Server_Exception If the GraphQL request fails.
	 */
	public function get_all(): Synonyms_List_Response {
		try {
			return ( new Get_Synonyms_Query() )->execute( $this->client, Synonyms_List_Response::class );
		} catch ( GraphQL_Exception $e ) {
			$this->logger->error( 'synonyms', $e->getMessage() );

			throw new Server_Exception( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * @param string      $synonyms Comma-separated synonyms string.
	 * @param string|null $id       Rule ID for updates, null for creation.
	 *
	 * @throws \WPE\AITK\WP\Rest\Exceptions\Server_Exception If the GraphQL request fails.
	 */
	public function save( string $synonyms, ?string $id = null ): Synonym_Save_Response {
		try {
			return ( new Save_Synonym_Mutation( $synonyms, $id ) )->execute(
				$this->client,
				Synonym_Save_Response::class
			);
		} catch ( GraphQL_Exception $e ) {
			$this->logger->error( 'synonyms', $e->getMessage() );

			throw new Server_Exception( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * @param string $id The rule ID to delete.
	 *
	 * @throws \WPE\AITK\WP\Rest\Exceptions\Server_Exception If the GraphQL request fails.
	 */
	public function delete( string $id ): Synonym_Delete_Response {
		try {
			return ( new Delete_Synonym_Mutation( $id ) )->execute( $this->client, Synonym_Delete_Response::class );
		} catch ( GraphQL_Exception $e ) {
			$this->logger->error( 'synonyms', $e->getMessage() );

			throw new Server_Exception( $e->getMessage(), $e->getCode(), $e );
		}
	}
}
