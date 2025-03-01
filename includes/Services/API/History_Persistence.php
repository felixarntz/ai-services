<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\History_Persistence
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API;

use Felix_Arntz\AI_Services\Services\API\Types\History;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Meta\Meta_Repository;

/**
 * Class for the history persistence layer.
 *
 * @since n.e.x.t
 */
class History_Persistence {

	/**
	 * The current user instance.
	 *
	 * @since n.e.x.t
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * User meta repository.
	 *
	 * @since n.e.x.t
	 * @var Meta_Repository
	 */
	private $meta_repository;

	/**
	 * Internal cache for history slugs, keyed by feature.
	 *
	 * @since n.e.x.t
	 * @var array<string, string[]>|null
	 */
	private $history_slugs;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Current_User    $current_user    The current user instance.
	 * @param Meta_Repository $meta_repository User meta repository.
	 */
	public function __construct( Current_User $current_user, Meta_Repository $meta_repository ) {
		$this->current_user    = $current_user;
		$this->meta_repository = $meta_repository;
	}

	/**
	 * Checks whether there is a history for a given feature and history slug.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param string $slug    Unique identifier of the history within the feature. Must only contain lowercase letters,
	 *                        numbers, hyphens.
	 * @return bool True if there is a history, false otherwise.
	 */
	public function has_history( string $feature, string $slug ): bool {
		if ( ! $this->is_valid_identifier( $feature ) || ! $this->is_valid_identifier( $slug ) ) {
			return false;
		}

		$history_slugs = $this->get_history_slugs();
		return isset( $history_slugs[ $feature ] ) && in_array( $slug, $history_slugs[ $feature ], true );
	}

	/**
	 * Loads the history for a given feature and history slug.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param string $slug    Unique identifier of the history within the feature. Must only contain lowercase letters,
	 *                        numbers, hyphens.
	 * @return History|null The history, or null if there is no history.
	 */
	public function load_history( string $feature, string $slug ): ?History {
		if ( ! $this->is_valid_identifier( $feature ) || ! $this->is_valid_identifier( $slug ) ) {
			return null;
		}

		$history_slugs = $this->get_history_slugs();
		if ( ! isset( $history_slugs[ $feature ] ) || ! in_array( $slug, $history_slugs[ $feature ], true ) ) {
			return null;
		}

		$history_data = (array) $this->meta_repository->get(
			$this->current_user->get_id(),
			$this->prefix_key_for_current_site( $this->to_key( $feature, $slug ) ),
			array()
		);

		$history_data['feature'] = $feature;
		$history_data['slug']    = $slug;

		return History::from_array( $history_data );
	}

	/**
	 * Saves the history for a given feature and history slug.
	 *
	 * @since n.e.x.t
	 *
	 * @param History $history The history to save. Must have a unique feature and history slug set.
	 * @return bool True on success, false on failure.
	 */
	public function save_history( History $history ): bool {
		$feature = $history->get_feature();
		$slug    = $history->get_slug();
		if ( ! $this->is_valid_identifier( $feature ) || ! $this->is_valid_identifier( $slug ) ) {
			return false;
		}

		$history_slugs = $this->get_history_slugs();
		if ( ! isset( $history_slugs[ $feature ] ) || ! in_array( $slug, $history_slugs[ $feature ], true ) ) {
			if ( ! isset( $history_slugs[ $feature ] ) ) {
				$history_slugs[ $feature ] = array();
			}
			$history_slugs[ $feature ][] = $slug;
			$this->amend_history_slugs( $history_slugs );
		}

		$history_data                = $history->to_array();
		$history_data['lastUpdated'] = current_time( 'mysql', true );
		unset( $history_data['feature'], $history_data['slug'] );

		return $this->meta_repository->update(
			$this->current_user->get_id(),
			$this->prefix_key_for_current_site( $this->to_key( $feature, $slug ) ),
			$history_data
		);
	}

	/**
	 * Clears the history for a given feature and history slug.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param string $slug    Unique identifier of the history within the feature. Must only contain lowercase letters,
	 *                        numbers, hyphens.
	 * @return bool True on success, false on failure.
	 */
	public function clear_history( string $feature, string $slug ): bool {
		if ( ! $this->is_valid_identifier( $feature ) || ! $this->is_valid_identifier( $slug ) ) {
			return false;
		}

		$history_slugs = $this->get_history_slugs();
		if ( ! isset( $history_slugs[ $feature ] ) || ! in_array( $slug, $history_slugs[ $feature ], true ) ) {
			// Consider nothing to clear a success.
			return true;
		}

		$history_slugs[ $feature ] = array_values( array_diff( $history_slugs[ $feature ], array( $slug ) ) );
		if ( count( $history_slugs[ $feature ] ) === 0 ) {
			unset( $history_slugs[ $feature ] );
		}
		$this->amend_history_slugs( $history_slugs );

		return $this->meta_repository->delete(
			$this->current_user->get_id(),
			$this->prefix_key_for_current_site( $this->to_key( $feature, $slug ) )
		);
	}

	/**
	 * Loads all histories for a given feature.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @return History[] All histories for the feature.
	 */
	public function load_histories_for_feature( string $feature ): array {
		if ( ! $this->is_valid_identifier( $feature ) ) {
			return array();
		}

		$history_slugs = $this->get_history_slugs();
		if ( ! isset( $history_slugs[ $feature ] ) ) {
			return array();
		}

		return array_filter(
			array_map(
				function ( string $slug ) use ( $feature ) {
					return $this->load_history( $feature, $slug );
				},
				$history_slugs[ $feature ]
			)
		);
	}

	/**
	 * Gets all history slugs for all features from the database.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, string[]> All history slugs, keyed by feature.
	 */
	private function get_history_slugs(): array {
		if ( null !== $this->history_slugs ) {
			return $this->history_slugs;
		}

		$this->meta_repository->set_single(
			$this->prefix_key_for_current_site( 'ais_history_keys' ),
			false
		);

		$history_keys = (array) $this->meta_repository->get(
			$this->current_user->get_id(),
			$this->prefix_key_for_current_site( 'ais_history_keys' )
		);

		$this->history_slugs = array_reduce(
			$history_keys,
			function ( array $history_slugs, string $key ) {
				list( $feature, $slug ) = $this->from_key( $key );
				if ( ! isset( $history_slugs[ $feature ] ) ) {
					$history_slugs[ $feature ] = array();
				}
				$history_slugs[ $feature ][] = $slug;
				return $history_slugs;
			},
			array()
		);
		return $this->history_slugs;
	}

	/**
	 * Amends the history slugs for all features and saved them in the database.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, string[]> $history_slugs All history slugs, keyed by feature.
	 */
	private function amend_history_slugs( array $history_slugs ): void {
		$this->history_slugs = $history_slugs;

		$history_keys = array();
		foreach ( $this->history_slugs as $feature => $ids ) {
			foreach ( $ids as $id ) {
				$history_keys[] = $this->to_key( $feature, $id );
			}
		}

		$this->meta_repository->set_single(
			$this->prefix_key_for_current_site( 'ais_history_keys' ),
			false
		);

		$this->meta_repository->update(
			$this->current_user->get_id(),
			$this->prefix_key_for_current_site( 'ais_history_keys' ),
			$history_keys
		);
	}

	/**
	 * Gets the feature and history slug from an internal chat history key.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $key History key.
	 * @return string[] Array with exactly two elements: feature and history slug.
	 */
	private function from_key( string $key ): array {
		$results = explode( '__', $key, 3 );
		array_shift( $results );
		return $results;
	}

	/**
	 * Gets the internal chat history key from a feature and history slug.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $feature Feature identifier. Must only contain lowercase letters, numbers, hyphens.
	 * @param string $slug    History slug. Must only contain lowercase letters, numbers, hyphens.
	 * @return string History key.
	 */
	private function to_key( string $feature, string $slug ): string {
		return 'ais_history__' . $feature . '__' . $slug;
	}

	/**
	 * Prefixes the given key with the current site's database prefix.
	 *
	 * This ensures that the key is unique across all sites in a multisite environment and therefore prevents histories
	 * from one site to be shared with all other sites.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $key Key.
	 * @return string Prefixed key.
	 */
	private function prefix_key_for_current_site( string $key ): string {
		global $wpdb;

		return $wpdb->get_blog_prefix() . $key;
	}

	/**
	 * Checks whether the given feature or chat identifier is valid.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $identifier Identifier. Must only contain lowercase letters,  numbers, hyphens.
	 * @return bool True if the identifier is valid, false otherwise.
	 */
	private function is_valid_identifier( string $identifier ): bool {
		if ( '' === $identifier ) {
			return false;
		}
		return (bool) preg_match( '/^[a-z0-9-]+$/', $identifier );
	}
}
