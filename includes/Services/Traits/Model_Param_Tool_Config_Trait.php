<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Model_Param_Tool_Config_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use InvalidArgumentException;

/**
 * Trait for a model that uses `Tool_Config`.
 *
 * @since n.e.x.t
 */
trait Model_Param_Tool_Config_Trait {

	/**
	 * The tool configuration.
	 *
	 * @since n.e.x.t
	 * @var Tool_Config|null
	 */
	private $tool_config;

	/**
	 * Gets the tool configuration.
	 *
	 * @since n.e.x.t
	 *
	 * @return Tool_Config|null The tool configuration, or null if not set.
	 */
	final protected function get_tool_config(): ?Tool_Config {
		return $this->tool_config;
	}

	/**
	 * Sets the tool configuration.
	 *
	 * @since n.e.x.t
	 *
	 * @param Tool_Config $tool_config The tool configuration.
	 */
	final protected function set_tool_config( Tool_Config $tool_config ): void {
		$this->tool_config = $tool_config;
	}

	/**
	 * Sets the tool configuration if provided in the `toolConfig` model parameter.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the `toolConfig` model parameter is invalid.
	 */
	protected function set_tool_config_from_model_params( array $model_params ): void {
		if ( ! isset( $model_params['toolConfig'] ) ) {
			return;
		}

		if ( is_array( $model_params['toolConfig'] ) ) {
			$model_params['toolConfig'] = Tool_Config::from_array( $model_params['toolConfig'] );
		}

		if ( ! $model_params['toolConfig'] instanceof Tool_Config ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid toolConfig model parameter: The value must be an array or an instance of %s.',
					Tool_Config::class
				)
			);
		}

		$this->set_tool_config( $model_params['toolConfig'] );
	}
}
