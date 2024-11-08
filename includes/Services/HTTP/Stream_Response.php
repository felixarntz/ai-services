<?php
/**
 * Class Felix_Arntz\AI_Services\Services\HTTP\Stream_Response
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\HTTP;

use Felix_Arntz\AI_Services\Services\HTTP\Contracts\With_Stream;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Generic_Response;
use Felix_Arntz\AI_Services_Dependencies\Psr\Http\Message\StreamInterface;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Class for a HTTP response that uses streaming.
 *
 * @since n.e.x.t
 *
 * @implements IteratorAggregate<Generator>
 */
class Stream_Response extends Generic_Response implements With_Stream, IteratorAggregate {

	/**
	 * The stream to read from.
	 *
	 * @since n.e.x.t
	 * @var StreamInterface|Stream
	 */
	private $stream;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param int                    $status  The HTTP status code received with the response.
	 * @param StreamInterface|Stream $stream  The response body stream to read from.
	 * @param array<string, string>  $headers The headers received with the response.
	 *
	 * @throws InvalidArgumentException Thrown if the $stream parameter has an invalid type.
	 */
	public function __construct( int $status, $stream, array $headers ) {
		parent::__construct( $status, '', $headers );

		if ( ! $stream instanceof StreamInterface && ! $stream instanceof Stream ) {
			throw new InvalidArgumentException(
				sprintf(
					'Stream must be an instance of %1$s or %2$s.',
					StreamInterface::class,
					Stream::class
				)
			);
		}

		$this->stream = $stream;
	}

	/**
	 * Returns a generator that reads individual chunks of decoded JSON data from the streamed response body.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generator The generator for the response stream.
	 */
	public function read_stream(): Generator {
		while ( ! $this->stream->eof() ) {
			$line = $this->read_line( $this->stream );
			$data = json_decode( $line, true );
			if ( ! $data ) {
				continue;
			}
			yield $data;
		}
	}

	/**
	 * Retrieves an iterator reading individual chunks of decoded JSON data from the streamed response body.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generator The iterator for the response stream.
	 */
	public function getIterator(): Generator {
		return $this->read_stream();
	}

	/**
	 * Reads a line from the stream.
	 *
	 * @since n.e.x.t
	 *
	 * @param StreamInterface|Stream $stream The stream to read from.
	 * @return string The line read from the stream.
	 */
	private function read_line( $stream ): string {
		$buffer = '';

		while ( ! $stream->eof() ) {
			$buffer .= $stream->read( 1 );

			if ( strlen( $buffer ) === 1 && '{' !== $buffer ) {
				$buffer = '';
			}

			if ( json_decode( $buffer ) !== null ) {
				return $buffer;
			}
		}

		return rtrim( $buffer, ']' );
	}
}
