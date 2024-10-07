<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Decorators\AI_Service_Decorator_With_API_Client
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Decorators;

use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use InvalidArgumentException;

/**
 * Class for an AI service that wraps another AI service with API client through a decorator pattern.
 *
 * This class effectively acts as middleware for the underlying AI service, allowing for additional functionality to be
 * centrally provided.
 *
 * @since 0.1.0
 */
class AI_Service_Decorator_With_API_Client extends AI_Service_Decorator implements With_API_Client {

	/**
	 * The underlying AI service to use.
	 *
	 * This is purely here to satisfy PHPStan requirements. The parent class has a private property with the same name,
	 * which therefore is separate and has a different expected type.
	 *
	 * @since 0.1.0
	 * @var Generative_AI_Service&With_API_Client
	 */
	private $service;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Generative_AI_Service $service The underlying AI service to use.
	 *
	 * @throws InvalidArgumentException Thrown if the service does not implement the With_API_Client interface.
	 */
	public function __construct( Generative_AI_Service $service ) {
		parent::__construct( $service );

		if ( ! $service instanceof With_API_Client ) {
			throw new InvalidArgumentException( 'The service must implement the With_API_Client interface.' );
		}

		// See above. This feels unnecessary, but it's required by PHPStan.
		$this->service = $service;
	}

	/**
	 * Gets the API client instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Generative_AI_API_Client The API client instance.
	 */
	public function get_api_client(): Generative_AI_API_Client {
		return $this->service->get_api_client();
	}
}
