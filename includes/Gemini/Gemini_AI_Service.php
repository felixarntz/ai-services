<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Generative_AI
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini;

use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Contracts\Generative_AI_Service;

/**
 * Class for the Gemini AI service.
 *
 * @since n.e.x.t
 */
class Gemini_AI_Service implements Generative_AI_Service {

	/**
	 * The Gemini API instance.
	 *
	 * @since n.e.x.t
	 * @var Gemini_API
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $api_key The API key.
	 */
	public function __construct( string $api_key ) {
		$this->api = new Gemini_API( $api_key );
	}

	/**
	 * Lists the available generative model slugs.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string[] The available model slugs.
	 */
	public function list_models( array $request_options = array() ): array {
		// TODO: Implement this.
		return array();
	}

	/**
	 * Checks if the generative model with the provided slug exists.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $model The model slug.
	 * @return bool True if the model exists, false otherwise.
	 */
	public function has_model( string $model ): bool {
		if ( ! str_contains( $model, '/' ) ) {
			$model = 'models/' . $model;
		}

		// TODO: Implement this.
		return false;
	}

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params    The model parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 */
	public function get_model( array $model_params, array $request_options = array() ): Generative_AI_Model {
		return new Gemini_AI_Model( $this->api, $model_params, $request_options );
	}
}
