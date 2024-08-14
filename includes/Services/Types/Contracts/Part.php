<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Types\Contracts\Part
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Types\Contracts;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

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
