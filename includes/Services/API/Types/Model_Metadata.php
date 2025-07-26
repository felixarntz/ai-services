<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Value class representing metadata about a generative AI model.
 *
 * @since 0.7.0
 */
final class Model_Metadata implements Arrayable, With_JSON_Schema {

	/**
	 * The model slug.
	 *
	 * @since 0.7.0
	 * @var string
	 */
	private $slug;

	/**
	 * The model name.
	 *
	 * @since 0.7.0
	 * @var string
	 */
	private $name;

	/**
	 * List of AI capabilities supported by the model.
	 *
	 * @since 0.7.0
	 * @var string[]
	 */
	private $capabilities;

	/**
	 * Constructor.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $args {
	 *     The arguments for the model metadata.
	 *
	 *     @type string $slug           The model slug.
	 *     @type string $name           Optional. The model name. Default will be generated from the slug.
	 *     @type string[] $capabilities Optional. The list of AI capabilities supported by the model.
	 *                                  Default empty array.
	 * }
	 *
	 * @throws InvalidArgumentException Thrown if the given slug is invalid.
	 */
	public function __construct( array $args ) {
		$args = $this->parse_args( $args );

		$this->slug         = $args['slug'];
		$this->name         = $args['name'];
		$this->capabilities = $args['capabilities'];
	}

	/**
	 * Gets the model slug.
	 *
	 * @since 0.7.0
	 *
	 * @return string The model slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets the model name.
	 *
	 * @since 0.7.0
	 *
	 * @return string The model name.
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Gets the list of AI capabilities supported by the model.
	 *
	 * @since 0.7.0
	 *
	 * @return string[] List of AI capabilities supported by the model.
	 */
	public function get_capabilities(): array {
		return $this->capabilities;
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The array representation.
	 */
	public function to_array(): array {
		return array(
			'slug'         => $this->slug,
			'name'         => $this->name,
			'capabilities' => $this->capabilities,
		);
	}

	/**
	 * Creates a Model_Metadata instance from an array of model metadata arguments.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $args The model metadata arguments.
	 * @return Model_Metadata The Model_Metadata instance.
	 */
	public static function from_array( array $args ): Model_Metadata {
		return new Model_Metadata( $args );
	}

	/**
	 * Parses the model metadata arguments.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $args The model metadata arguments.
	 * @return array<string, mixed> The parsed model metadata arguments.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid argument is provided.
	 */
	private function parse_args( array $args ): array {
		if ( ! isset( $args['slug'] ) ) {
			throw new InvalidArgumentException( 'The slug is required.' );
		}

		if ( isset( $args['name'] ) ) {
			$args['name'] = (string) $args['name'];
		} else {
			$args['name'] = ucwords( str_replace( array( '-', '_' ), ' ', $args['slug'] ) );
		}

		if ( isset( $args['capabilities'] ) ) {
			if ( ! is_array( $args['capabilities'] ) ) {
				throw new InvalidArgumentException( 'The capabilities must be an array.' );
			}
			foreach ( $args['capabilities'] as $capability ) {
				if ( ! AI_Capability::is_valid_value( $capability ) ) {
					throw new InvalidArgumentException( 'The capabilities contain an invalid value.' );
				}
			}
		} else {
			$args['capabilities'] = array();
		}

		return $args;
	}

	/**
	 * Returns the JSON schema for the model metadata.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'slug'         => array(
					'description' => __( 'Unique model slug.', 'ai-services' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'name'         => array(
					'description' => __( 'User-facing model name.', 'ai-services' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'capabilities' => array(
					'description' => __( 'List of AI capabilities supported by the model.', 'ai-services' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
						'enum' => AI_Capability::get_values(),
					),
					'readonly'    => true,
				),
			),
			'additionalProperties' => false,
		);
	}
}
