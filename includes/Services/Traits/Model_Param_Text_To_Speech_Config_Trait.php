<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Model_Param_Text_To_Speech_Config_Trait
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Text_To_Speech_Config;
use InvalidArgumentException;

/**
 * Trait for a model that uses `Text_To_Speech_Config`.
 *
 * @since 0.7.0
 */
trait Model_Param_Text_To_Speech_Config_Trait {

	/**
	 * The text to speech configuration.
	 *
	 * @since 0.7.0
	 * @var Text_To_Speech_Config|null
	 */
	private $text_to_speech_config;

	/**
	 * Gets the text to speech configuration.
	 *
	 * @since 0.7.0
	 *
	 * @return Text_To_Speech_Config|null The text to speech configuration, or null if not set.
	 */
	final protected function get_text_to_speech_config(): ?Text_To_Speech_Config {
		return $this->text_to_speech_config;
	}

	/**
	 * Sets the text to speech configuration.
	 *
	 * @since 0.7.0
	 *
	 * @param Text_To_Speech_Config $text_to_speech_config The text to speech configuration.
	 */
	final protected function set_text_to_speech_config( Text_To_Speech_Config $text_to_speech_config ): void {
		$this->text_to_speech_config = $text_to_speech_config;
	}

	/**
	 * Sets the text to speech configuration if provided in the `generationConfig` model parameter.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the `generationConfig` model parameter is invalid.
	 */
	protected function set_text_to_speech_config_from_model_params( array $model_params ): void {
		if ( ! isset( $model_params['generationConfig'] ) ) {
			return;
		}

		if ( is_array( $model_params['generationConfig'] ) ) {
			$model_params['generationConfig'] = Text_To_Speech_Config::from_array( $model_params['generationConfig'] );
		}

		if ( ! $model_params['generationConfig'] instanceof Text_To_Speech_Config ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid generationConfig model parameter: The value must be an array or an instance of %s.',
					Text_To_Speech_Config::class
				)
			);
		}

		$this->set_text_to_speech_config( $model_params['generationConfig'] );
	}
}
