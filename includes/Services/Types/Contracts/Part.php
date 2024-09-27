<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Types\Contracts\Part
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types\Contracts;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Interface for a class representing a part of content for a generative model.
 *
 * @since n.e.x.t
 */
interface Part extends Arrayable {

	/**
	 * Sets data for the part.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The part data.
	 */
	public function set_data( array $data ): void;
}
