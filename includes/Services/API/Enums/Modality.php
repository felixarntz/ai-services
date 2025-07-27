<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\Modality
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

/**
 * Class for the modality enum.
 *
 * @since 0.7.0
 */
final class Modality extends Abstract_Enum {

	const TEXT  = 'text';
	const IMAGE = 'image';
	const AUDIO = 'audio';

	/**
	 * Gets all values for the enum.
	 *
	 * @since 0.7.0
	 *
	 * @return string[] The list of all values.
	 */
	protected static function get_all_values(): array {
		return array(
			self::TEXT,
			self::IMAGE,
			self::AUDIO,
		);
	}
}
