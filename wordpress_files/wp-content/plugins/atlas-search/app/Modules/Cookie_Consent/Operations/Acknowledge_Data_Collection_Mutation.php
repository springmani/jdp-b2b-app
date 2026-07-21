<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent\Operations;

use WPE\AITK\WP\GraphQL\Abstract_GraphQL_Mutation;
use WPE\AITK\WP\GraphQL\GraphQL_Endpoint;

final class Acknowledge_Data_Collection_Mutation extends Abstract_GraphQL_Mutation {
	private const MUTATION = '
		mutation AcknowledgeDataCollection($userData: UserData!) {
			dataCollection {
				acknowledgeDataCollection(userData: $userData) {
					code
					success
					message
				}
			}
		}
	';

	private string $site_url;
	private string $user_id;

	public function __construct( string $site_url, string $user_id ) {
		$this->site_url = $site_url;
		$this->user_id  = $user_id;
	}

	protected function endpoint(): string {
		return GraphQL_Endpoint::SITES;
	}

	protected function query(): string {
		return self::MUTATION;
	}

	/**
	 * @inheritdoc
	 */
	protected function variables(): array {
		return [
			'userData' => [
				'siteUrl' => $this->site_url,
				'userID'  => $this->user_id,
			],
		];
	}

	protected function response_path(): string {
		return 'dataCollection.acknowledgeDataCollection';
	}
}
