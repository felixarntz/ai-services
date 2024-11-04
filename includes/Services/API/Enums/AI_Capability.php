<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

/**
 * Class for the AI capability enum.
 *
 * @since 0.2.0
 */
final class AI_Capability extends Abstract_Enum {

	const IMAGE_GENERATION = 'image_generation';
	const MULTIMODAL_INPUT = 'multimodal_input';
	const TEXT_GENERATION  = 'text_generation';

	/**
	 * Gets all values for the enum.
	 *
	 * @since 0.2.0
	 *
	 * @return string[] The list of all values.
	 */
	protected static function get_all_values(): array {
		return array(
			self::IMAGE_GENERATION,
			self::MULTIMODAL_INPUT,
			self::TEXT_GENERATION,
		);
	}
}
