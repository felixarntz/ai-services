<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Model_Param_Image_Generation_Config_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use InvalidArgumentException;

/**
 * Trait for a model that uses `Image_Generation_Config`.
 *
 * @since n.e.x.t
 */
trait Model_Param_Image_Generation_Config_Trait {

	/**
	 * The image generation configuration.
	 *
	 * @since n.e.x.t
	 * @var Image_Generation_Config|null
	 */
	private $image_generation_config;

	/**
	 * Gets the image generation configuration.
	 *
	 * @since n.e.x.t
	 *
	 * @return Image_Generation_Config|null The image generation configuration, or null if not set.
	 */
	final protected function get_image_generation_config(): ?Image_Generation_Config {
		return $this->image_generation_config;
	}

	/**
	 * Sets the image generation configuration.
	 *
	 * @since n.e.x.t
	 *
	 * @param Image_Generation_Config $image_generation_config The image generation configuration.
	 */
	final protected function set_image_generation_config( Image_Generation_Config $image_generation_config ): void {
		$this->image_generation_config = $image_generation_config;
	}

	/**
	 * Sets the image generation configuration if provided in the `generationConfig` model parameter.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the `generationConfig` model parameter is invalid.
	 */
	protected function set_image_generation_config_from_model_params( array $model_params ): void {
		if ( ! isset( $model_params['generationConfig'] ) ) {
			return;
		}

		if ( is_array( $model_params['generationConfig'] ) ) {
			$model_params['generationConfig'] = Image_Generation_Config::from_array( $model_params['generationConfig'] );
		}

		if ( ! $model_params['generationConfig'] instanceof Image_Generation_Config ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid generationConfig model parameter: The value must be an array or an instance of %s.',
					Image_Generation_Config::class
				)
			);
		}

		$this->set_image_generation_config( $model_params['generationConfig'] );
	}
}
