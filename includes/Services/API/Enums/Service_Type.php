<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\Service_Type
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

/**
 * Class for the service type enum.
 *
 * @since n.e.x.t
 */
final class Service_Type extends Abstract_Enum {

	const CLOUD  = 'cloud';
	const SERVER = 'server';
	const CLIENT = 'client';

	/**
	 * Gets all values for the enum.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The list of all values.
	 */
	protected static function get_all_values(): array {
		return array(
			self::CLOUD,
			self::SERVER,
			self::CLIENT,
		);
	}
}
