<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\Data_Encryption
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

/**
 * Class responsible for encrypting and decrypting data.
 *
 * @since 0.1.0
 * @see https://felix-arntz.me/blog/storing-confidential-data-in-wordpress/
 */
final class Data_Encryption {

	/**
	 * Key to use for encryption.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $key;

	/**
	 * Salt to use for encryption.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $salt;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param ?string $key  Optional. Key to use for encryption. If not passed, the default key determined by constants
	 *                      will be used.
	 * @param ?string $salt Optional. Salt to use for encryption. If not passed, the default salt determined by
	 *                      constants will be used.
	 */
	public function __construct( ?string $key = null, ?string $salt = null ) {
		$this->key  = $key ?? $this->get_default_key();
		$this->salt = $salt ?? $this->get_default_salt();
	}

	/**
	 * Encrypts a value.
	 *
	 * If a user-based key is set, that key is used. Otherwise the default key is used.
	 *
	 * @since 0.1.0
	 *
	 * @param string $value Value to encrypt.
	 * @return string Encrypted value, or empty string on failure.
	 */
	public function encrypt( string $value ): string {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $value;
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
		if ( ! $raw_value ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $raw_value );
	}

	/**
	 * Decrypts a value.
	 *
	 * If a user-based key is set, that key is used. Otherwise the default key is used.
	 *
	 * @since 0.1.0
	 *
	 * @param string $raw_value Value to decrypt.
	 * @return string Decrypted value, or empty string on failure.
	 */
	public function decrypt( string $raw_value ): string {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $raw_value;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded_value = base64_decode( $raw_value, true );

		if ( false === $decoded_value ) {
			return '';
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = substr( $decoded_value, 0, $ivlen );

		$decoded_value = substr( $decoded_value, $ivlen );

		$value = openssl_decrypt( $decoded_value, $method, $this->key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
			return '';
		}

		return substr( $value, 0, - strlen( $this->salt ) );
	}

	/**
	 * Gets the default encryption key to use.
	 *
	 * @since 0.1.0
	 *
	 * @return string Default (not user-based) encryption key.
	 */
	private function get_default_key(): string {
		if ( defined( 'AI_SERVICES_ENCRYPTION_KEY' ) && '' !== AI_SERVICES_ENCRYPTION_KEY ) {
			return AI_SERVICES_ENCRYPTION_KEY;
		}

		if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
			return LOGGED_IN_KEY;
		}

		// If this is reached, you're either not on a live site or have a serious security issue.
		return 'test-key';
	}

	/**
	 * Gets the default encryption salt to use.
	 *
	 * @since 0.1.0
	 *
	 * @return string Encryption salt.
	 */
	private function get_default_salt(): string {
		if ( defined( 'AI_SERVICES_ENCRYPTION_SALT' ) && '' !== AI_SERVICES_ENCRYPTION_SALT ) {
			return AI_SERVICES_ENCRYPTION_SALT;
		}

		if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
			return LOGGED_IN_SALT;
		}

		// If this is reached, you're either not on a live site or have a serious security issue.
		return 'test-salt';
	}
}
