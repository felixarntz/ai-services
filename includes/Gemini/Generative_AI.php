<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Generative_AI
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini;

/**
 * Top-level class for the Generative AI SDK, inspired by https://www.npmjs.com/package/@google/generative-ai.
 *
 * @since n.e.x.t
 */
class Generative_AI {

	/**
	 * The Gemini API key.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $api_key The API key
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Get a generative model instance for the provided model name.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params    The model parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_Model The generative model.
	 */
	public function get_generative_model( array $model_params, array $request_options = array() ): Generative_Model {
		return new Generative_Model( $this->api_key, $model_params, $request_options );
	}
}
