<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use InvalidArgumentException;

/**
 * Class representing an OpenAI AI model.
 *
 * @since 0.1.0
 * @since 0.5.0 Deprecated in favor of the `OpenAI_AI_Text_Generation_Model` class.
 */
class OpenAI_AI_Model extends OpenAI_AI_Text_Generation_Model {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param OpenAI_AI_API_Client $api             The OpenAI AI API instance.
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see OpenAI_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( OpenAI_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		if ( function_exists( '_deprecated_class' ) ) {
			_deprecated_class( __CLASS__, '0.5.0', OpenAI_AI_Text_Generation_Model::class );
		} elseif ( WP_DEBUG ) {
			$message = sprintf(
				'Class %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.',
				__CLASS__,
				'0.5.0',
				OpenAI_AI_Text_Generation_Model::class
			);
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error, WordPress.Security.EscapeOutput.OutputNotEscaped
			trigger_error( $message, E_USER_DEPRECATED );
		}

		parent::__construct( $api, $model, $model_params, $request_options );
	}
}
