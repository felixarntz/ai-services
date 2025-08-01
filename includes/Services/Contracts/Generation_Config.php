<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\Generation_Config
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Interface for a class representing configuration options for a generative AI model.
 *
 * @since 0.7.0
 */
interface Generation_Config extends Arrayable, With_JSON_Schema {

	/**
	 * Returns the value for the given supported argument.
	 *
	 * @since 0.7.0
	 *
	 * @param string $name The argument name.
	 * @return mixed The argument value, or its default value if not set.
	 */
	public function get_arg( string $name );

	/**
	 * Returns all formally supported arguments.
	 *
	 * Only includes arguments that have an explicit value set, i.e. not defaults.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The arguments.
	 */
	public function get_args(): array;

	/**
	 * Returns the additional arguments.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The additional arguments.
	 */
	public function get_additional_args(): array;
}
