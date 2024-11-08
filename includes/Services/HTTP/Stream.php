<?php
/**
 * Class Felix_Arntz\AI_Services\Services\HTTP\Stream
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\HTTP;

use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class representing a stream.
 *
 * This is more or less a copy of GuzzleHttp\Psr7\Stream, but simplified.
 *
 * @since n.e.x.t
 */
final class Stream {

	private const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';
	private const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

	/**
	 * The wrapped stream.
	 *
	 * @since n.e.x.t
	 * @var resource|null
	 */
	private $stream;

	/**
	 * Whether the stream is seekable.
	 *
	 * @since n.e.x.t
	 * @var bool
	 */
	private $seekable;

	/**
	 * Whether the stream is readable.
	 *
	 * @since n.e.x.t
	 * @var bool
	 */
	private $readable;

	/**
	 * Whether the stream is writable.
	 *
	 * @since n.e.x.t
	 * @var bool
	 */
	private $writable;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param resource $stream  Stream resource to wrap.
	 *
	 * @throws InvalidArgumentException Thrown if the stream is not a stream resource.
	 */
	public function __construct( $stream ) {
		if ( ! is_resource( $stream ) ) {
			throw new InvalidArgumentException( 'Stream must be a resource' );
		}

		$this->stream = $stream;

		$meta           = stream_get_meta_data( $this->stream );
		$this->seekable = $meta['seekable'];
		$this->readable = (bool) preg_match( self::READABLE_MODES, $meta['mode'] );
		$this->writable = (bool) preg_match( self::WRITABLE_MODES, $meta['mode'] );
	}

	/**
	 * Closes the stream when the destructed.
	 *
	 * @since n.e.x.t
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Closes the stream and then detaches it.
	 *
	 * @since n.e.x.t
	 */
	public function close(): void {
		if ( ! isset( $this->stream ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $this->stream );
		$this->detach();
	}

	/**
	 * Detaches the stream, unless it was already detached.
	 *
	 * @since n.e.x.t
	 *
	 * @return resource|null The detached stream resource, or null if the stream was already detached.
	 */
	public function detach() {
		if ( ! isset( $this->stream ) ) {
			return null;
		}

		$result = $this->stream;
		unset( $this->stream );

		$this->seekable = false;
		$this->readable = false;
		$this->writable = false;

		return $result;
	}

	/**
	 * Checks whether the stream is seekable.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the stream is seekable, false otherwise.
	 */
	public function is_seekable(): bool {
		return $this->seekable;
	}

	/**
	 * Checks whether the stream is readable.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the stream is readable, false otherwise.
	 */
	public function is_readable(): bool {
		return $this->readable;
	}

	/**
	 * Checks whether the stream is writable.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the stream is writable, false otherwise.
	 */
	public function is_writable(): bool {
		return $this->writable;
	}

	/**
	 * Checks whether the end of the stream is reached.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the end of the stream is reached, false otherwise.
	 *
	 * @throws RuntimeException Thrown if the stream is detached.
	 */
	public function eof(): bool {
		if ( ! isset( $this->stream ) ) {
			throw new RuntimeException( 'Stream is detached' );
		}

		return feof( $this->stream );
	}

	/**
	 * Returns the current position of the stream.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The current position of the stream.
	 *
	 * @throws RuntimeException Thrown if the stream is detached or the position cannot be determined.
	 */
	public function tell(): int {
		if ( ! isset( $this->stream ) ) {
			throw new RuntimeException( 'Stream is detached' );
		}

		$result = ftell( $this->stream );

		if ( false === $result ) {
			throw new RuntimeException( 'Unable to determine stream position' );
		}

		return $result;
	}

	/**
	 * Rewinds the stream to its starting position.
	 *
	 * @since n.e.x.t
	 */
	public function rewind(): void {
		$this->seek( 0 );
	}

	/**
	 * Moves the stream pointer to a new position.
	 *
	 * @since n.e.x.t
	 *
	 * @param int $offset The stream offset to move to.
	 *
	 * @throws RuntimeException Thrown if the stream is detached, not seekable, or if the position cannot be reached.
	 */
	public function seek( int $offset ): void {
		if ( ! isset( $this->stream ) ) {
			throw new RuntimeException( 'Stream is detached' );
		}
		if ( ! $this->seekable ) {
			throw new RuntimeException( 'Stream is not seekable' );
		}
		if ( fseek( $this->stream, $offset ) === -1 ) {
			throw new RuntimeException( 'Unable to seek to stream position ' . (int) $offset );
		}
	}

	/**
	 * Reads data from the stream.
	 *
	 * @since n.e.x.t
	 *
	 * @param int $length The number of bytes to read.
	 * @return string The data read from the stream.
	 *
	 * @throws RuntimeException Thrown if the stream is detached, not readable, or if the length is negative.
	 */
	public function read( int $length ): string {
		if ( ! isset( $this->stream ) ) {
			throw new RuntimeException( 'Stream is detached' );
		}
		if ( ! $this->readable ) {
			throw new RuntimeException( 'Cannot read from non-readable stream' );
		}
		if ( $length < 0 ) {
			throw new RuntimeException( 'Length parameter cannot be negative' );
		}

		if ( 0 === $length ) {
			return '';
		}

		try {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
			$contents = fread( $this->stream, $length );
		} catch ( Exception $e ) {
			throw new RuntimeException( 'Unable to read from stream' );
		}

		if ( false === $contents ) {
			throw new RuntimeException( 'Unable to read from stream' );
		}

		return $contents;
	}

	/**
	 * Writes data to the stream.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $contents The data to write.
	 * @return int The number of bytes written to the stream.
	 *
	 * @throws RuntimeException Thrown if the stream is detached, not writable, or if the length is negative.
	 */
	public function write( string $contents ): int {
		if ( ! isset( $this->stream ) ) {
			throw new RuntimeException( 'Stream is detached' );
		}
		if ( ! $this->writable ) {
			throw new RuntimeException( 'Cannot write to a non-writable stream' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		$result = fwrite( $this->stream, $contents );

		if ( false === $result ) {
			throw new RuntimeException( 'Unable to write to stream' );
		}

		return $result;
	}
}
