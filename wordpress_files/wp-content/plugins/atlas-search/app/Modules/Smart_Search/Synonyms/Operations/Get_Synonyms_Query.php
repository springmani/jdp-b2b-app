<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Operations;

use WPE\AITK\WP\GraphQL\Abstract_GraphQL_Query;

/**
 * Fetches all synonym rules from the backend.
 */
final class Get_Synonyms_Query extends Abstract_GraphQL_Query {
	private const QUERY = '
		query GetSynonyms {
			config {
				synonyms {
					rules {
						total
						rules {
							id
							synonyms
						}
					}
				}
			}
		}
	';

	protected function query(): string {
		return self::QUERY;
	}

	protected function response_path(): string {
		return 'config.synonyms.rules';
	}
}
