<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Operations;

use WPE\AITK\WP\GraphQL\Abstract_GraphQL_Mutation;

/**
 * Creates or updates a synonym rule.
 */
final class Save_Synonym_Mutation extends Abstract_GraphQL_Mutation {
	private const MUTATION = '
		mutation SaveRule($id: ID, $synonyms: String!) {
			config {
				synonyms {
					saveRule(id: $id, synonyms: $synonyms) {
						success
						code
						message
						rule {
							id
							synonyms
						}
					}
				}
			}
		}
	';

	private string $synonyms;

	private ?string $id;

	public function __construct( string $synonyms, ?string $id = null ) {
		$this->synonyms = $synonyms;
		$this->id       = $id;
	}

	protected function query(): string {
		return self::MUTATION;
	}

	/**
	 * @inheritdoc
	 */
	protected function variables(): array {
		$variables = [ 'synonyms' => $this->synonyms ];

		if ( null !== $this->id && '' !== $this->id ) {
			$variables['id'] = $this->id;
		}

		return $variables;
	}

	protected function response_path(): string {
		return 'config.synonyms.saveRule';
	}
}
