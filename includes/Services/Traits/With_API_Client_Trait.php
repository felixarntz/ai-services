<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use RuntimeException;

/**
 * Trait for a service or model which implements the With_API_Client interface.
 *
 * @since n.e.x.t
 */
trait With_API_Client_Trait {

	/**
	 * The AI API client instance.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_API_Client
	 */
	private $api_client;

	/**
	 * Gets the API client instance.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generative_AI_API_Client The API client instance.
	 *
	 * @throws RuntimeException Thrown if the API client is not set.
	 */
	final public function get_api_client(): Generative_AI_API_Client {
		if ( ! $this->api_client instanceof Generative_AI_API_Client ) {
			throw new RuntimeException( 'API client must be set in the constructor.' );
		}

		return $this->api_client;
	}

	/**
	 * Sets the API client instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_API_Client $api_client The API client instance.
	 */
	final protected function set_api_client( Generative_AI_API_Client $api_client ): void {
		$this->api_client = $api_client;
	}
}
