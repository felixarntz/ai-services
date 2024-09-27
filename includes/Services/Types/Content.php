<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Content
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Class representing an entry of content for a generative AI model.
 *
 * @since n.e.x.t
 */
final class Content implements Arrayable {

	const ROLE_USER   = 'user';
	const ROLE_MODEL  = 'model';
	const ROLE_SYSTEM = 'system';

	/**
	 * The role of the content.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $role;

	/**
	 * The parts of the content.
	 *
	 * @since n.e.x.t
	 * @var Parts
	 */
	private $parts;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $role  The role of the content.
	 * @param Parts  $parts The parts of the content.
	 *
	 * @throws InvalidArgumentException Thrown if the given role is invalid.
	 */
	public function __construct( string $role, Parts $parts ) {
		if ( ! $this->is_valid_role( $role ) ) {
			throw new InvalidArgumentException(
				esc_html(
					sprintf(
						/* translators: %s: invalid role encountered */
						__( 'The role %s is invalid.', 'ai-services' ),
						$role
					)
				)
			);
		}

		$this->role  = $role;
		$this->parts = $parts;
	}

	/**
	 * Gets the role of the content.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The role of the content.
	 */
	public function get_role(): string {
		return $this->role;
	}

	/**
	 * Gets the parts of the content.
	 *
	 * @since n.e.x.t
	 *
	 * @return Parts The parts of the content.
	 */
	public function get_parts(): Parts {
		return $this->parts;
	}

	/**
	 * Returns the array representation.
	 *
	 * @since n.e.x.t
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return array(
			'role'  => $this->role,
			'parts' => $this->parts->to_array(),
		);
	}

	/**
	 * Creates a Content instance from an array of content data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Content Content instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Content {
		if ( ! isset( $data['role'], $data['parts'] ) ) {
			throw new InvalidArgumentException( 'Content data must contain role and parts.' );
		}

		return new Content( $data['role'], Parts::from_array( $data['parts'] ) );
	}

	/**
	 * Checks if the given role is valid.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $role The role to check.
	 * @return bool True if the role is valid, false otherwise.
	 */
	private function is_valid_role( string $role ): bool {
		return in_array(
			$role,
			array(
				self::ROLE_USER,
				self::ROLE_MODEL,
				self::ROLE_SYSTEM,
			),
			true
		);
	}
}
