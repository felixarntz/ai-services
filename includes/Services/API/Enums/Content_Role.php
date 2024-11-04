<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\Content_Role
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

/**
 * Class for the content role enum.
 *
 * @since n.e.x.t
 */
final class Content_Role extends Abstract_Enum {

	const USER   = 'user';
	const MODEL  = 'model';
	const SYSTEM = 'system';

	/**
	 * Gets all values for the enum.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The list of all values.
	 */
	protected static function get_all_values(): array {
		return array(
			self::USER,
			self::MODEL,
			self::SYSTEM,
		);
	}
}
