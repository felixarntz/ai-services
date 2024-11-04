<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Candidate
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Class representing a candidate for a content response from a generative AI model.
 *
 * @since 0.1.0
 */
final class Candidate implements Arrayable {

	/**
	 * The content.
	 *
	 * @since 0.1.0
	 * @var Content
	 */
	private $content;

	/**
	 * Additional data for the candidate, if any.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $additional_data;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Content              $content         The content.
	 * @param array<string, mixed> $additional_data Additional data for the candidate, if any.
	 */
	public function __construct( Content $content, array $additional_data = array() ) {
		$this->content = $content;

		// Remove the content from the additional data, if present, to prevent conflicts.
		unset( $additional_data['content'] );
		$this->additional_data = $additional_data;
	}

	/**
	 * Gets the content.
	 *
	 * @since 0.1.0
	 *
	 * @return Content The content.
	 */
	public function get_content(): Content {
		return $this->content;
	}

	/**
	 * Gets a field value from the additional data.
	 *
	 * @since 0.1.0
	 *
	 * @param string $field The field name.
	 * @return mixed|null The field value, or null if not found.
	 */
	public function get_field_value( string $field ) {
		if ( isset( $this->additional_data[ $field ] ) ) {
			return $this->additional_data[ $field ];
		}

		if ( str_contains( $field, '_' ) ) {
			$camel_case_field = $this->underscore_to_camel_case( $field );
			if ( isset( $this->additional_data[ $camel_case_field ] ) ) {
				return $this->additional_data[ $camel_case_field ];
			}
		}

		/*
		 * A few common special cases.
		 * For instance, "finish_reason" is sometimes called "stop_reason".
		 */
		switch ( $field ) {
			case 'finish_reason':
				return $this->get_field_value( 'stop_reason' );
			case 'finishReason':
				return $this->get_field_value( 'stopReason' );
		}

		return null;
	}

	/**
	 * Gets the additional data.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> The additional data.
	 */
	public function get_additional_data(): array {
		return $this->additional_data;
	}

	/**
	 * Converts the candidate to an array.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> The array representation of the candidate.
	 */
	public function to_array(): array {
		return array_merge(
			array(
				'content' => $this->content->to_array(),
			),
			$this->additional_data
		);
	}

	/**
	 * Creates a Candidate instance from an array of content data.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Candidate Candidate instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Candidate {
		if ( ! isset( $data['content'] ) ) {
			throw new InvalidArgumentException( 'Candidate data must contain content.' );
		}

		/*
		 * Apparently, the API sometimes omits this.
		 * Given candidates are always part of a model response, we can safely assume the role is 'model'.
		 */
		if ( ! isset( $data['content']['role'] ) ) {
			$data['content']['role'] = Content_Role::MODEL;
		}

		$content = Content::from_array( $data['content'] );
		unset( $data['content'] );

		return new Candidate( $content, $data );
	}

	/**
	 * Transforms a snake_case string to camelCase.
	 *
	 * @since 0.1.0
	 *
	 * @param string $input The snake_case string.
	 * @return string The camelCase string.
	 */
	private function underscore_to_camel_case( string $input ): string {
		return lcfirst( str_replace( '_', '', ucwords( $input, '_' ) ) );
	}
}
