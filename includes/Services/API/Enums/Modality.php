<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\Modality
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

/**
 * Class for the modality enum.
 *
 * @since n.e.x.t
 */
final class Modality extends Abstract_Enum {

	const TEXT  = 'text';
	const IMAGE = 'image';
	const AUDIO = 'audio';

	/**
	 * Gets all values for the enum.
	 *
	 * @since n.e.x.t
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
