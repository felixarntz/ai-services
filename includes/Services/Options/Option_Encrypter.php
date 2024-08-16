<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Options\Option_Encrypter
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Options;

use Vendor_NS\WP_Starter_Plugin\Services\Util\Data_Encryption;

/**
 * Class that allows for options to be encrypted when stored in the database as well as decrypted when retrieved.
 *
 * @since n.e.x.t
 */
final class Option_Encrypter {

	const ENCRYPTION_PREFIX = 'enc::';

	/**
	 * The data encryption instance.
	 *
	 * @since n.e.x.t
	 * @var Data_Encryption
	 */
	private $data_encryption;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Data_Encryption $data_encryption The data encryption instance.
	 */
	public function __construct( Data_Encryption $data_encryption ) {
		$this->data_encryption = $data_encryption;
	}

	/**
	 * Adds relevant hooks to handle encryption and decryption of the given option.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $option_slug The option to use encryption with.
	 */
	public function add_encryption_hooks( string $option_slug ): void {
		add_filter( "sanitize_option_{$option_slug}", array( $this, 'encrypt_option' ), 9999, 2 ); // Encrypt late.
		add_filter( "option_{$option_slug}", array( $this, 'decrypt_option' ), -9999, 2 ); // Decrypt early.
	}

	/**
	 * Checks if the given option has encryption enabled.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $option_slug The identifier/name of the option.
	 * @return bool True if the option has encryption enabled, false otherwise.
	 */
	public function has_encryption( string $option_slug ): bool {
		return (
			has_filter( "sanitize_option_{$option_slug}", array( $this, 'encrypt_option' ) ) &&
			has_filter( "option_{$option_slug}", array( $this, 'decrypt_option' ) )
		);
	}

	/**
	 * Encrypts the given option value.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed  $value       The option value to encrypt.
	 * @param string $option_slug The identifier/name of the option.
	 * @return string Encrypted option value.
	 */
	public function encrypt_option( $value, string $option_slug ): string {
		// Bail if the value is already encrypted.
		if ( is_string( $value ) && str_starts_with( $value, self::ENCRYPTION_PREFIX ) ) {
			return $value;
		}

		$encrypted = $this->data_encryption->encrypt( maybe_serialize( $value ) );

		// If encryption fails, trigger a warning but continue with the unencrypted value. Better not to lose data.
		if ( ! $encrypted ) {
			$this->trigger_error(
				__METHOD__,
				sprintf(
					/* translators: %s: Option slug */
					__( 'Failed to encrypt the value for the option "%s".', 'wp-starter-plugin' ),
					$option_slug
				)
			);
			return $value;
		}

		return self::ENCRYPTION_PREFIX . $encrypted;
	}

	/**
	 * Decrypts the given option value.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed  $value       The option value to decrypt.
	 * @param string $option_slug The identifier/name of the option.
	 * @return mixed Decrypted option value.
	 */
	public function decrypt_option( $value, string $option_slug ) {
		// Bail if the value is already decrypted.
		if ( ! is_string( $value ) || ! str_starts_with( $value, self::ENCRYPTION_PREFIX ) ) {
			return $value;
		}

		$decrypted = $this->data_encryption->decrypt( substr( $value, strlen( self::ENCRYPTION_PREFIX ) ) );

		// If decryption fails, trigger a warning and return an empty string.
		if ( ! $decrypted ) {
			$this->trigger_error(
				__METHOD__,
				sprintf(
					/* translators: %s: Option slug */
					__( 'Failed to decrypt the value for the option "%s".', 'wp-starter-plugin' ),
					$option_slug
				)
			);
			return '';
		}

		return maybe_unserialize( $decrypted );
	}

	/**
	 * Triggers an error, if WP_DEBUG is enabled.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $function_name The name of the function that triggered the error.
	 * @param string $message       The message explaining the error.
	 * @param int    $error_level   Optional. The designated error type for this error.
	 *                              Only works with E_USER family of constants. Default E_USER_NOTICE.
	 */
	private function trigger_error( string $function_name, string $message, int $error_level = E_USER_NOTICE ): void {
		// The wp_trigger_error() function was only added in WordPress 6.4, so this is a minimal shim.
		if ( ! function_exists( 'wp_trigger_error' ) ) {
			if ( ! WP_DEBUG ) {
				return;
			}
			if ( ! empty( $function_name ) ) {
				$message = sprintf( '%s(): %s', $function_name, $message );
			}
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( esc_html( $message ), $error_level );
			return;
		}

		wp_trigger_error( $function_name, $message, $error_level );
	}
}
