<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Blob
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use InvalidArgumentException;

/**
 * Simple value class representing a binary data blob, e.g. from a file.
 *
 * @since 0.5.0
 */
final class Blob {

	/**
	 * The binary data of the blob.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $binary_data;

	/**
	 * The MIME type of the blob.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $mime_type;

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param string $binary_data The binary data of the blob.
	 * @param string $mime_type   The MIME type of the blob.
	 */
	public function __construct( string $binary_data, string $mime_type ) {
		$this->binary_data = $binary_data;
		$this->mime_type   = $mime_type;
	}

	/**
	 * Retrieves the binary data of the blob.
	 *
	 * @since 0.5.0
	 *
	 * @return string The binary data.
	 */
	public function get_binary_data(): string {
		return $this->binary_data;
	}

	/**
	 * Retrieves the MIME type of the blob.
	 *
	 * @since 0.5.0
	 *
	 * @return string The MIME type.
	 */
	public function get_mime_type(): string {
		return $this->mime_type;
	}

	/**
	 * Creates a new blob instance from a file.
	 *
	 * @since 0.5.0
	 *
	 * @param string $file      The file path or URL.
	 * @param string $mime_type Optional. MIME type, to override the automatic detection. Default empty string.
	 * @return Blob The blob instance.
	 *
	 * @throws InvalidArgumentException Thrown if the file could not be read or if the MIME type cannot be determined.
	 */
	public static function from_file( string $file, string $mime_type = '' ): self {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$blob = file_get_contents( $file );
		if ( ! $blob ) {
			throw new InvalidArgumentException(
				sprintf(
					'Could not read file %s.',
					htmlspecialchars( $file ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}

		if ( ! $mime_type ) {
			$file_type = wp_check_filetype( $file );
			if ( ! $file_type['type'] ) {
				throw new InvalidArgumentException(
					sprintf(
						'Could not determine MIME type of file %s.',
						htmlspecialchars( $file ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					)
				);
			}
			$mime_type = $file_type['type'];
		}

		return new self( $blob, $mime_type );
	}
}
