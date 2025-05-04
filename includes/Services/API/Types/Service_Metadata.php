<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Value class representing metadata about a generative AI service.
 *
 * @since n.e.x.t
 */
final class Service_Metadata implements Arrayable, With_JSON_Schema {

	/**
	 * The service slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $slug;

	/**
	 * The service name.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $name;

	/**
	 * The service credentials URL.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $credentials_url;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $args {
	 *     The arguments for the service metadata.
	 *
	 *     @type string $slug            The service slug.
	 *     @type string $name            Optional. The service name.
	 *     @type string $credentials_url Optional. The service credentials URL.
	 * }
	 *
	 * @throws InvalidArgumentException Thrown if the given slug is invalid.
	 */
	public function __construct( array $args ) {
		$args = $this->parse_args( $args );

		$this->slug            = $args['slug'];
		$this->name            = $args['name'];
		$this->credentials_url = $args['credentials_url'];
	}

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets the service name.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service name.
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Gets the service credentials URL.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service credentials URL.
	 */
	public function get_credentials_url(): string {
		return $this->credentials_url;
	}

	/**
	 * Returns the array representation.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The array representation.
	 */
	public function to_array(): array {
		return array(
			'slug'            => $this->slug,
			'name'            => $this->name,
			'credentials_url' => $this->credentials_url,
		);
	}

	/**
	 * Creates a Service_Metadata instance from an array of service metadata arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $args The service metadata arguments.
	 * @return Service_Metadata The Service_Metadata instance.
	 */
	public static function from_array( array $args ): Service_Metadata {
		return new Service_Metadata( $args );
	}

	/**
	 * Parses the service metadata arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $args The service metadata arguments.
	 * @return array<string, mixed> The parsed service metadata arguments.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid argument is provided.
	 */
	private function parse_args( array $args ): array {
		if ( ! isset( $args['slug'] ) ) {
			throw new InvalidArgumentException( 'The slug is required.' );
		}

		if ( ! preg_match( '/^[a-z0-9-]+$/', $args['slug'] ) ) {
			throw new InvalidArgumentException(
				'The service slug must only contain lowercase letters, numbers, and hyphens.'
			);
		}

		if ( isset( $args['name'] ) ) {
			$args['name'] = (string) $args['name'];
		} else {
			$args['name'] = ucwords( str_replace( array( '-', '_' ), ' ', $args['slug'] ) );
		}

		if ( isset( $args['credentials_url'] ) ) {
			$args['credentials_url'] = (string) $args['credentials_url'];

			// Basic sanity check to ensure a protocol is present.
			if ( ! str_contains( $args['credentials_url'], ':' ) && ! in_array( $args['credentials_url'][0], array( '/', '#', '?' ), true ) ) {
				$args['credentials_url'] = 'https://' . $args['credentials_url'];
			}
		} else {
			$args['credentials_url'] = '';
		}

		return $args;
	}

	/**
	 * Returns the JSON schema for the service metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'slug'            => array(
					'description' => __( 'Unique service slug.', 'ai-services' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'name'            => array(
					'description' => __( 'User-facing service name.', 'ai-services' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'credentials_url' => array(
					'description' => __( 'Service credentials URL, or empty string if not specified.', 'ai-services' ),
					'type'        => 'string',
					'readonly'    => true,
				),
			),
			'additionalProperties' => false,
		);
	}
}
