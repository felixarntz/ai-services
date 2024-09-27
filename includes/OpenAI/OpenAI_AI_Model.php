<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Model
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Types\Candidates;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use InvalidArgumentException;

/**
 * Class representing an OpenAI AI model.
 *
 * @since n.e.x.t
 */
class OpenAI_AI_Model implements Generative_AI_Model, With_Text_Generation {
	use With_Text_Generation_Trait;

	/**
	 * The model slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $model;

	/**
	 * The generation configuration.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $generation_config; /* @phpstan-ignore property.onlyWritten  */

	/**
	 * The system instruction.
	 *
	 * @since n.e.x.t
	 * @var Content|null
	 */
	private $system_instruction; /* @phpstan-ignore property.onlyWritten  */

	/**
	 * The request options.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $request_options; /* @phpstan-ignore property.onlyWritten  */

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see OpenAI_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameter is missing.
	 */
	public function __construct( string $model, array $model_params = array(), array $request_options = array() ) {
		// TODO: Require the API client object as first parameter and set it as class property.
		$this->request_options = $request_options;

		$this->model = $model;

		$this->generation_config = $model_params['generation_config'] ?? array();

		if ( isset( $model_params['system_instruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['system_instruction'] );
		}
	}

	/**
	 * Gets the model slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The model slug.
	 */
	public function get_model_slug(): string {
		return $this->model;
	}

	/**
	 * Sends a request to generate text content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_text_request( array $contents, array $request_options ): Candidates {
		// TODO: Implement this.
		return Candidates::from_array( array() );
	}
}
