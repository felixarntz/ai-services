<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

/**
 * Interface for a class that provides a JSON schema for its input.
 *
 * @since 0.2.0
 */
interface With_JSON_Schema {

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array;
}
