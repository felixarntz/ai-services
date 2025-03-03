<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Contracts;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Interface for a class representing a tool for a generative model.
 *
 * @since 0.5.0
 */
interface Tool extends Arrayable {

	/**
	 * Sets data for the tool.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The tool data.
	 */
	public function set_data( array $data ): void;
}
