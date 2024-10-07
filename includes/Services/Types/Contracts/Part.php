<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Types\Contracts\Part
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types\Contracts;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Interface for a class representing a part of content for a generative model.
 *
 * @since 0.1.0
 */
interface Part extends Arrayable {

	/**
	 * Sets data for the part.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $data The part data.
	 */
	public function set_data( array $data ): void;
}
