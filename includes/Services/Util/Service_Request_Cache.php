<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Util\Service_Request_Cache
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Util;

use Exception;
use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;

/**
 * Class that allows to wrap service method calls so that their return values are cached.
 *
 * @since n.e.x.t
 */
final class Service_Request_Cache {

	/**
	 * Wraps the given method call in a WordPress transient so that its return value is cached.
	 *
	 * The transient name is generated based on the method name and arguments. It is unique per service and method
	 * name, so that different services can have methods with the same name that are cached separately. It also
	 * includes a timestamp of when the service configuration was last changed, so that the cache is invalidated as
	 * needed.
	 *
	 * If the method throws an exception, the exception is cached as well, so that it can be rethrown on subsequent
	 * calls.
	 *
	 * The transient is stored for 24 hours.
	 *
	 * @since n.e.x.t
	 *
	 * @param string   $service_slug Service slug.
	 * @param callable $method       Method to cache.
	 * @param mixed[]  $args         Optional. Method arguments. Default empty array.
	 * @return mixed Method return value, potentially served from cache.
	 *
	 * @throws Exception Rethrown original exception from the method call, if there was one.
	 */
	public static function wrap_transient( string $service_slug, callable $method, array $args = array() ) {
		$key          = self::get_cache_key( $method, $args );
		$last_changed = self::get_last_changed( $service_slug );

		$transient_name = "{$service_slug}:{$key}:{$last_changed}";

		$value = get_transient( $transient_name );
		if ( false === $value ) {
			$value = self::call_method( $method, $args );
			set_transient( $transient_name, self::sanitize_value_for_cache( $value ), DAY_IN_SECONDS );
		} else {
			$value = self::parse_value_from_cache( $value );
		}
		if ( $value instanceof Exception ) {
			throw $value;
		}
		return $value;
	}

	/**
	 * Wraps the given method call in the WordPress object cache so that its return value is cached.
	 *
	 * The cache key is generated based on the method name and arguments. It is unique per service and method name,
	 * so that different services can have methods with the same name that are cached separately. It also includes
	 * a timestamp of when the service configuration was last changed, so that the cache is invalidated as needed.
	 *
	 * The service slug is used as the cache group.
	 *
	 * If the method throws an exception, the exception is cached as well, so that it can be rethrown on subsequent
	 * calls.
	 *
	 * The cached value is stored for 24 hours.
	 *
	 * @since n.e.x.t
	 *
	 * @param string   $service_slug Service slug.
	 * @param callable $method       Method to cache.
	 * @param mixed[]  $args         Optional. Method arguments. Default empty array.
	 * @return mixed Method return value, potentially served from cache.
	 *
	 * @throws Exception Rethrown original exception from the method call, if there was one.
	 */
	public static function wrap_cache( string $service_slug, callable $method, array $args = array() ) {
		$key          = self::get_cache_key( $method, $args );
		$last_changed = self::get_last_changed( $service_slug );

		$cache_name = "{$key}:{$last_changed}";

		$value = wp_cache_get( $cache_name, $service_slug );
		if ( false === $value ) {
			$value = self::call_method( $method, $args );
			wp_cache_set( $cache_name, self::sanitize_value_for_cache( $value ), $service_slug, DAY_IN_SECONDS );
		} else {
			$value = self::parse_value_from_cache( $value );
		}
		if ( $value instanceof Exception ) {
			throw $value;
		}
		return $value;
	}

	/**
	 * Invalidates the caches for a service.
	 *
	 * This method should be called whenever the configuration of a service changes, so that the caches are invalidated
	 * and the next request will fetch fresh data. This encompasses both transients and the object cache.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $service_slug Service slug.
	 */
	public static function invalidate_caches( string $service_slug ): void {
		self::set_last_changed( $service_slug );

		// Not strictly necessary, but if we can clean up, let's do so.
		if (
			function_exists( 'wp_cache_flush_group' ) &&
			function_exists( 'wp_cache_supports' ) &&
			wp_cache_supports( 'flush_group' )
		) {
			wp_cache_flush_group( $service_slug );
		}
	}

	/**
	 * Calls the given method with the given arguments, catching any exceptions that are thrown.
	 *
	 * If an exception is thrown, it will be returned instead of the method's return value.
	 *
	 * @since n.e.x.t
	 *
	 * @param callable $method Method to call.
	 * @param mixed[]  $args   Method arguments.
	 * @return mixed Method return value or exception.
	 */
	private static function call_method( callable $method, array $args ) {
		try {
			return call_user_func_array( $method, $args );
		} catch ( Exception $e ) {
			return $e;
		}
	}

	/**
	 * Sanitizes the given value to be stored in the cache.
	 *
	 * If the value is an exception, it is converted to an array with the exception class name and message.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed $value Value to sanitize.
	 * @return mixed Sanitized value.
	 */
	private static function sanitize_value_for_cache( $value ) {
		if ( is_object( $value ) && $value instanceof Exception ) {
			return array(
				'classname' => get_class( $value ),
				'message'   => $value->getMessage(),
			);
		}
		return $value;
	}

	/**
	 * Parses the given value from the cache.
	 *
	 * This converts any sanitized exceptions back to their original exception form.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed $value Value from the cache.
	 * @return mixed Parsed value.
	 */
	private static function parse_value_from_cache( $value ) {
		if ( is_array( $value ) && isset( $value['classname'], $value['message'] ) ) {
			$class = $value['classname'];
			if ( ! class_exists( $class ) ) { // This should never be true, but a reasonable safeguard.
				$class = Exception::class;
			}
			$message = $value['message'];
			return new $class( $message );
		}
		return $value;
	}

	/**
	 * Gets the cache key for a method call.
	 *
	 * The returned key does not include the service slug, so the service slug has to be separately included as part of
	 * the identifier for where to cache the value.
	 *
	 * @since n.e.x.t
	 *
	 * @param callable $method Method to cache.
	 * @param mixed[]  $args   Optional. Method arguments. Default empty array.
	 * @return string Cache key.
	 *
	 * @throws InvalidArgumentException Thrown if the method is not a method on a service or model instance.
	 */
	private static function get_cache_key( callable $method, array $args = array() ): string {
		if ( ! is_array( $method ) || ! is_object( $method[0] ) || ! is_string( $method[1] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'Only methods on service and model instances can be cached.', 'wp-starter-plugin' )
			);
		}

		if ( $method[0] instanceof Generative_AI_Service ) {
			$type = 'service';
		} elseif ( $method[0] instanceof Generative_AI_Model ) {
			$type = 'model';
		} else {
			throw new InvalidArgumentException(
				esc_html__( 'Only methods on service and model instances can be cached.', 'wp-starter-plugin' )
			);
		}

		return $type . ':' . self::get_cache_hash( $method[1], $args );
	}

	/**
	 * Gets the cache hash for a method call.
	 *
	 * @since n.e.x.t
	 *
	 * @param string  $method_name Method name.
	 * @param mixed[] $args        Optional. Method arguments. Default empty array.
	 * @return string Cache hash.
	 */
	private static function get_cache_hash( string $method_name, array $args = array() ): string {
		$hash = $method_name;
		if ( ! empty( $args ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			$hash .= '_' . md5( serialize( $args ) );
		}
		return $hash;
	}

	/**
	 * Gets the last changed value for a service.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $service_slug Service slug.
	 * @return string UNIX timestamp for when the configuration of the service was last changed.
	 */
	private static function get_last_changed( string $service_slug ): string {
		if ( wp_using_ext_object_cache() ) {
			return wp_cache_get_last_changed( $service_slug );
		}

		$last_changed_option = (array) get_option( 'wpsp_services_last_changed', array() );
		if ( ! isset( $last_changed_option[ $service_slug ] ) ) {
			$last_changed_option[ $service_slug ] = microtime();
			update_option( 'wpsp_services_last_changed', $last_changed_option );
		}
		return $last_changed_option[ $service_slug ];
	}

	/**
	 * Sets the last changed value for a service to the current UNIX timestamp.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $service_slug Service slug.
	 */
	private static function set_last_changed( string $service_slug ): void {
		if ( wp_using_ext_object_cache() ) {
			wp_cache_set_last_changed( $service_slug );
			return;
		}

		$last_changed_option                  = (array) get_option( 'wpsp_services_last_changed', array() );
		$last_changed_option[ $service_slug ] = microtime();
		update_option( 'wpsp_services_last_changed', $last_changed_option );
	}
}
