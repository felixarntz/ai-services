<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Model_Param_Text_Generation_Config_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;
use InvalidArgumentException;

/**
 * Trait for a model that uses `Text_Generation_Config`.
 *
 * @since n.e.x.t
 */
trait Model_Param_Text_Generation_Config_Trait {

	/**
	 * The text generation configuration.
	 *
	 * @since n.e.x.t
	 * @var Text_Generation_Config|null
	 */
	private $text_generation_config;

	/**
	 * Gets the text generation configuration.
	 *
	 * @since n.e.x.t
	 *
	 * @return Text_Generation_Config|null The text generation configuration, or null if not set.
	 */
	final protected function get_text_generation_config(): ?Text_Generation_Config {
		return $this->text_generation_config;
	}

	/**
	 * Sets the text generation configuration.
	 *
	 * @since n.e.x.t
	 *
	 * @param Text_Generation_Config $text_generation_config The text generation configuration.
	 */
	final protected function set_text_generation_config( Text_Generation_Config $text_generation_config ): void {
		$this->text_generation_config = $text_generation_config;
	}

	/**
	 * Sets the text generation configuration if provided in the `generationConfig` model parameter.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the `generationConfig` model parameter is invalid.
	 */
	protected function set_text_generation_config_from_model_params( array $model_params ): void {
		if ( ! isset( $model_params['generationConfig'] ) ) {
			return;
		}

		if ( is_array( $model_params['generationConfig'] ) ) {
			$model_params['generationConfig'] = Text_Generation_Config::from_array( $model_params['generationConfig'] );
		}

		if ( ! $model_params['generationConfig'] instanceof Text_Generation_Config ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid generationConfig model parameter: The value must be an array or an instance of %s.',
					Text_Generation_Config::class
				)
			);
		}

		$this->set_text_generation_config( $model_params['generationConfig'] );
	}
}
