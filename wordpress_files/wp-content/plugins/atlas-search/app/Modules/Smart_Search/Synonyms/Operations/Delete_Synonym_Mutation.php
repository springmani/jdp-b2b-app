<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Operations;

use WPE\AITK\WP\GraphQL\Abstract_GraphQL_Mutation;

/**
 * Deletes a synonym rule by ID.
 */
final class Delete_Synonym_Mutation extends Abstract_GraphQL_Mutation {
	private const MUTATION = '
		mutation DeleteRule($id: ID!) {
			config {
				synonyms {
					deleteRule(id: $id) {
						success
						message
					}
				}
			}
		}
	';

	private string $id;

	public function __construct( string $id ) {
		$this->id = $id;
	}

	protected function query(): string {
		return self::MUTATION;
	}

	/**
	 * @inheritDoc
	 */
	protected function variables(): array {
		return [ 'id' => $this->id ];
	}

	protected function response_path(): string {
		return 'config.synonyms.deleteRule';
	}
}
