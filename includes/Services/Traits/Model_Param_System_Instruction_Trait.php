<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Model_Param_System_Instruction_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use InvalidArgumentException;

/**
 * Trait for a model that uses a system instruction.
 *
 * @since n.e.x.t
 */
trait Model_Param_System_Instruction_Trait {

	/**
	 * The system instruction.
	 *
	 * @since n.e.x.t
	 * @var Content|null
	 */
	private $system_instruction;

	/**
	 * Gets the system instruction.
	 *
	 * @since n.e.x.t
	 *
	 * @return Content|null The system instruction, or null if not set.
	 */
	final protected function get_system_instruction(): ?Content {
		return $this->system_instruction;
	}

	/**
	 * Sets the system instruction.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content $system_instruction The system instruction.
	 */
	final protected function set_system_instruction( Content $system_instruction ): void {
		$this->system_instruction = $system_instruction;
	}

	/**
	 * Sets the system instruction if provided in the `systemInstruction` model parameter.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the `systemInstruction` model parameter is invalid.
	 */
	protected function set_system_instruction_from_model_params( array $model_params ): void {
		if ( ! isset( $model_params['systemInstruction'] ) ) {
			return;
		}

		try {
			$model_params['systemInstruction'] = Formatter::format_system_instruction( $model_params['systemInstruction'] );
		} catch ( InvalidArgumentException $e ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid systemInstruction model parameter: %s',
					htmlspecialchars( $e->getMessage() ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}

		$this->set_system_instruction( $model_params['systemInstruction'] );
	}
}
