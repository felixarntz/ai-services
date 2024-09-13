<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Services_API_Instance
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services;

use RuntimeException;

/**
 * Class to provide singleton-like access to the canonical Services_API instance.
 *
 * @since n.e.x.t
 */
final class Services_API_Instance {

	/**
	 * Retrieve the canonical Services_API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API|null The canonical Services_API instance.
	 */
	private static $instance;

	/**
	 * Retrieves the canonical Services_API instance.
	 *
	 * @since n.e.x.t
	 *
	 * @return Services_API The canonical Services_API instance.
	 *
	 * @throws RuntimeException Thrown if the method is called too early when no instance has been set before.
	 */
	public static function get(): Services_API {
		if ( ! isset( self::$instance ) ) {
			throw new RuntimeException(
				esc_html__( 'Cannot get Services_API instance before it was set.', 'ai-services' )
			);
		}

		return self::$instance;
	}

	/**
	 * Sets the canonical Services_API instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param Services_API $instance The canonical Services_API instance.
	 *
	 * @throws RuntimeException Thrown if the method is called after the instance has already been set.
	 */
	public static function set( Services_API $instance ): void {
		if ( isset( self::$instance ) ) {
			throw new RuntimeException(
				esc_html__( 'Cannot set Services_API instance after it has already been set.', 'ai-services' )
			);
		}

		self::$instance = $instance;
	}
}
