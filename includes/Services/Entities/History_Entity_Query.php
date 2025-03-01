<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Entities\History_Entity_Query
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Entities;

use Felix_Arntz\AI_Services\Services\API\History_Persistence;
use Felix_Arntz\AI_Services\Services\API\Types\History;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Contracts\Entity_Query;

/**
 * Class representing a history entity query for the REST API.
 *
 * @since n.e.x.t
 */
class History_Entity_Query implements Entity_Query {

	/**
	 * The history persistence instance.
	 *
	 * @since n.e.x.t
	 * @var History_Persistence
	 */
	private $history_persistence;

	/**
	 * Query arguments.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $query_args;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param History_Persistence  $history_persistence The history persistence instance.
	 * @param array<string, mixed> $query_args          {
	 *     The query arguments.
	 *
	 *     @type string   $feature The feature to limit results to, required. Default empty string.
	 *     @type string[] $slugs   Array of history slugs to limit results to. Default empty array.
	 *     @type string   $orderby Field to order results by. Either 'slug', 'created', or 'lastUpdated'.
	 *                             Default 'created'.
	 *     @type string   $order   Order of results. Either 'ASC' or 'DESC'. Default 'ASC'.
	 *     @type int      $number  Number of histories to limit results to. Default 10.
	 *     @type int      $offset  Offset at which to start with the results. Default 0.
	 * }
	 */
	public function __construct( History_Persistence $history_persistence, array $query_args ) {
		$this->history_persistence = $history_persistence;
		$this->query_args          = $this->parse_defaults( $query_args );
	}

	/**
	 * Queries histories.
	 *
	 * @since n.e.x.t
	 *
	 * @return History_Entity[] List of history entities.
	 */
	public function get_entities(): array {
		$histories = $this->query_filtered_histories();

		// The default value of 'created' relies on the order in which the histories were added to the database.
		if ( 'slug' === $this->query_args['orderby'] ) {
			usort(
				$histories,
				function ( History $a, History $b ) {
					return $a->get_slug() <=> $b->get_slug();
				}
			);
		} elseif ( 'lastUpdated' === $this->query_args['orderby'] ) {
			usort(
				$histories,
				function ( History $a, History $b ) {
					return $a->get_last_updated() <=> $b->get_last_updated();
				}
			);
		}

		// If the order is descending, simply reverse the result from before.
		if ( 'DESC' === $this->query_args['order'] ) {
			$histories = array_reverse( $histories );
		}

		$histories = array_slice( $histories, $this->query_args['offset'], $this->query_args['number'] );

		return array_map(
			function ( History $history ) {
				return new History_Entity( $history );
			},
			$histories
		);
	}

	/**
	 * Queries history IDs.
	 *
	 * @since n.e.x.t
	 *
	 * @return int[] Empty array, as histories do not have IDs.
	 */
	public function get_ids(): array {
		return array();
	}

	/**
	 * Queries the history count.
	 *
	 * @since n.e.x.t
	 *
	 * @return int Service count.
	 */
	public function get_count(): int {
		$histories = $this->query_filtered_histories();
		return count( $histories );
	}

	/**
	 * Queries the filtered histories.
	 *
	 * This essentially retrieves all relevant histories without any ordering or limiting.
	 *
	 * @since n.e.x.t
	 *
	 * @return History[] Filtered histories based on the current query arguments.
	 */
	private function query_filtered_histories(): array {
		$histories = $this->history_persistence->load_histories_for_feature( $this->query_args['feature'] );

		if ( count( $this->query_args['slugs'] ) > 0 ) {
			$histories = array_filter(
				$histories,
				function ( History $history ) {
					return in_array( $history->get_slug(), $this->query_args['slugs'], true );
				}
			);
		}

		return $histories;
	}

	/**
	 * Parses and sanitizes the given query arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $query_args Query arguments.
	 * @return array<string, mixed> Query arguments parsed with defaults.
	 */
	private function parse_defaults( array $query_args ): array {
		$defaults = array(
			'feature' => '',
			'slugs'   => array(),
			'orderby' => 'created',
			'order'   => 'ASC',
			'number'  => 10,
			'offset'  => 0,
		);

		// Parse defaults and strip any keys that are not allowed.
		$query_args = wp_parse_args( $query_args, $defaults );
		$query_args = wp_array_slice_assoc( $query_args, array_keys( $defaults ) );

		$query_args['order'] = strtoupper( $query_args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		return $query_args;
	}
}
