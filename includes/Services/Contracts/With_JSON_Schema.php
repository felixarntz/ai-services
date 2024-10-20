<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

/**
 * Interface for a class that provides a JSON schema for its input.
 *
 * @since n.e.x.t
 */
interface With_JSON_Schema {

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array;
}
