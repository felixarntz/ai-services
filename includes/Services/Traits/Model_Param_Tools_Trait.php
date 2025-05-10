<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Model_Param_Tools_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use InvalidArgumentException;

/**
 * Trait for a model that uses `Tools`.
 *
 * @since n.e.x.t
 */
trait Model_Param_Tools_Trait {

	/**
	 * The tools instance.
	 *
	 * @since n.e.x.t
	 * @var Tools|null
	 */
	private $tools;

	/**
	 * Gets the tools instance.
	 *
	 * @since n.e.x.t
	 *
	 * @return Tools|null The tools instance, or null if not set.
	 */
	final protected function get_tools(): ?Tools {
		return $this->tools;
	}

	/**
	 * Sets the tools instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param Tools $tools The tools instance.
	 */
	final protected function set_tools( Tools $tools ): void {
		$this->tools = $tools;
	}

	/**
	 * Sets the tools instance if provided in the `tools` model parameter.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the `tools` model parameter is invalid.
	 */
	protected function set_tools_from_model_params( array $model_params ): void {
		if ( ! isset( $model_params['tools'] ) ) {
			return;
		}

		if ( is_array( $model_params['tools'] ) ) {
			$model_params['tools'] = Tools::from_array( $model_params['tools'] );
		}

		if ( ! $model_params['tools'] instanceof Tools ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid tools model parameter: The value must be an array or an instance of %s.',
					Tools::class
				)
			);
		}

		$this->set_tools( $model_params['tools'] );
	}
}
