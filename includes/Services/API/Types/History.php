<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\History
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use InvalidArgumentException;

/**
 * Class representing a chat history.
 *
 * @since 0.5.0
 */
final class History {

	/**
	 * The feature the history is associated with.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $feature;

	/**
	 * The history slug, unique within the feature.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $slug;

	/**
	 * When the history was last updated, as MySQL datetime string in GMT.
	 *
	 * @since 0.5.0
	 * @var string
	 */
	private $last_updated;

	/**
	 * The history entries.
	 *
	 * @since 0.5.0
	 * @var History_Entry[]
	 */
	private $entries;

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param string          $feature      The feature the history is associated with.
	 * @param string          $slug         The history slug.
	 * @param string          $last_updated When the history was last updated, as MySQL datetime string in GMT.
	 * @param History_Entry[] $entries      The history entries.
	 */
	public function __construct( string $feature, string $slug, string $last_updated, array $entries ) {
		$this->feature      = $feature;
		$this->slug         = $slug;
		$this->last_updated = $last_updated;
		$this->entries      = $entries;
	}

	/**
	 * Gets the feature the history is associated with.
	 *
	 * @since 0.5.0
	 *
	 * @return string The feature.
	 */
	public function get_feature(): string {
		return $this->feature;
	}

	/**
	 * Gets the history slug.
	 *
	 * @since 0.5.0
	 *
	 * @return string The history slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets when the history was last updated.
	 *
	 * @since 0.5.0
	 *
	 * @return string The last updated MySQL datetime string in GMT.
	 */
	public function get_last_updated(): string {
		return $this->last_updated;
	}

	/**
	 * Gets the history entries.
	 *
	 * @since 0.5.0
	 *
	 * @return History_Entry[] The history entries.
	 */
	public function get_entries(): array {
		return $this->entries;
	}

	/**
	 * Sets the history entries.
	 *
	 * @since 0.5.0
	 *
	 * @param History_Entry[]|array<string, mixed>[] $entries The history entries.
	 */
	public function set_entries( array $entries ): void {
		$this->entries = array_map(
			function ( $entry_data ) {
				if ( ! $entry_data instanceof History_Entry ) {
					return History_Entry::from_array( $entry_data );
				}
				return $entry_data;
			},
			$entries
		);
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.5.0
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return array(
			'feature'     => $this->feature,
			'slug'        => $this->slug,
			'lastUpdated' => $this->last_updated,
			'entries'     => array_map(
				function ( History_Entry $entry ) {
					return $entry->to_array();
				},
				$this->entries
			),
		);
	}

	/**
	 * Creates a History instance from an array of history data.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The history data.
	 * @return History History instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): History {
		if ( ! isset( $data['feature'], $data['slug'], $data['lastUpdated'], $data['entries'] ) ) {
			throw new InvalidArgumentException( 'History data must contain feature, slug, lastUpdated, and entries.' );
		}

		return new History(
			$data['feature'],
			$data['slug'],
			$data['lastUpdated'],
			array_map(
				function ( array $entry_data ) {
					return History_Entry::from_array( $entry_data );
				},
				$data['entries']
			)
		);
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'feature'     => array(
					'description' => __( 'Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'slug'        => array(
					'description' => __( 'Unique identifier of the history within the feature. Must only contain lowercase letters, numbers, hyphens.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'lastUpdated' => array(
					'description' => __( 'When the history was last updated, as MySQL datetime string in GMT.', 'ai-services' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'entries'     => array(
					'description' => __( 'The history entries, in ascending order.', 'ai-services' ),
					'type'        => 'array',
					'items'       => History_Entry::get_json_schema(),
					'context'     => array( 'view', 'edit' ),
				),
			),
		);
	}
}
