<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Entities\History_Entity
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Entities;

use Felix_Arntz\AI_Services\Services\API\Types\History;
use Felix_Arntz\AI_Services\Services\API\Types\History_Entry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Contracts\Entity;

/**
 * Class representing a history entity for the REST API.
 *
 * @since n.e.x.t
 */
class History_Entity implements Entity {

	/**
	 * The history object.
	 *
	 * @since n.e.x.t
	 * @var History
	 */
	private $history;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param History $history The underlying history object.
	 */
	public function __construct( History $history ) {
		$this->history = $history;
	}

	/**
	 * Gets the entity ID.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The entity ID.
	 */
	public function get_id(): int {
		return 0; // Unused, as histories use slugs instead of numeric identifiers.
	}

	/**
	 * Checks whether the entity is publicly accessible.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the entity is public, false otherwise.
	 */
	public function is_public(): bool {
		return false;
	}

	/**
	 * Gets the entity's primary URL.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Primary entity URL, or empty string if none.
	 */
	public function get_url(): string {
		return '';
	}

	/**
	 * Gets the entity's edit URL, if the current user is able to edit it.
	 *
	 * @since n.e.x.t
	 *
	 * @return string URL to edit the entity, or empty string if unable to edit.
	 */
	public function get_edit_url(): string {
		return '';
	}

	/**
	 * Gets the value for the given field of the entity.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $field The field identifier.
	 * @return mixed Value for the field, `null` if not set.
	 */
	public function get_field_value( string $field ) {
		switch ( $field ) {
			case 'feature':
				return $this->history->get_feature();
			case 'slug':
				return $this->history->get_slug();
			case 'lastUpdated':
				return $this->history->get_last_updated();
			case 'entries':
				return array_map(
					function ( History_Entry $entry ) {
						return $entry->to_array();
					},
					$this->history->get_entries()
				);
		}
		return null;
	}
}
