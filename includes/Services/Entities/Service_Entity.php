<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Entities\Service_Entity
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Entities;

use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Authentication\API_Key_Authentication;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Contracts\Entity;

/**
 * Class representing a service entity for the REST API.
 *
 * @since 0.1.0
 * @since 0.2.0 Moved from Felix_Arntz\AI_Services\Services\REST_Routes namespace.
 */
class Service_Entity implements Entity {

	/**
	 * The services API instance.
	 *
	 * @since 0.1.0
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * The service slug.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $slug;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Services_API $services_api The services API instance.
	 * @param string       $slug         The service slug.
	 */
	public function __construct( Services_API $services_api, string $slug ) {
		$this->services_api = $services_api;
		$this->slug         = $slug;
	}

	/**
	 * Gets the entity ID.
	 *
	 * @since 0.1.0
	 *
	 * @return int The entity ID.
	 */
	public function get_id(): int {
		return 0; // Unused, as services use slugs instead of numeric identifiers.
	}

	/**
	 * Checks whether the entity is publicly accessible.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if the entity is public, false otherwise.
	 */
	public function is_public(): bool {
		return false;
	}

	/**
	 * Gets the entity's primary URL.
	 *
	 * @since 0.1.0
	 *
	 * @return string Primary entity URL, or empty string if none.
	 */
	public function get_url(): string {
		return '';
	}

	/**
	 * Gets the entity's edit URL, if the current user is able to edit it.
	 *
	 * @since 0.1.0
	 *
	 * @return string URL to edit the entity, or empty string if unable to edit.
	 */
	public function get_edit_url(): string {
		return '';
	}

	/**
	 * Gets the value for the given field of the entity.
	 *
	 * @since 0.1.0
	 *
	 * @param string $field The field identifier.
	 * @return mixed Value for the field, `null` if not set.
	 */
	public function get_field_value( string $field ) {
		$metadata_schema = Service_Metadata::get_json_schema();

		switch ( $field ) {
			case 'slug':
				return $this->slug;
			case 'metadata':
				$metadata = $this->services_api->get_service_metadata( $this->slug );
				if ( ! $metadata ) {
					return null;
				}
				return $metadata->to_array();
			case 'is_available':
				return $this->services_api->is_service_available( $this->slug );
			case 'capabilities':
				return $this->get_capabilities();
			case 'available_models':
				$models = $this->get_available_models();
				return array_map(
					static function ( $model_metadata ) {
						// @phpstan-ignore-next-line
						if ( $model_metadata instanceof Model_Metadata ) {
							// This check is only here for backward compatibility. TODO: Remove in the next major.
							return $model_metadata->to_array();
						}
						// @phpstan-ignore-next-line
						return $model_metadata;
					},
					$models
				);
			case 'has_forced_api_key':
				return $this->has_forced_api_key();
			case 'authentication_option_slugs':
				// For now, API keys are the only supported authentication method. TODO: This needs to be revised.
				return array_keys( API_Key_Authentication::get_option_definitions( $this->slug ) );
			default:
				if ( isset( $metadata_schema['properties'][ $field ] ) ) {
					$metadata = $this->services_api->get_service_metadata( $this->slug );
					if ( ! $metadata ) {
						return '';
					}
					$metadata = $metadata->to_array();
					return isset( $metadata[ $field ] ) ? $metadata[ $field ] : '';
				}
		}
		return null;
	}

	/**
	 * Gets the AI capabilities that the service supports
	 *
	 * @since 0.1.0
	 *
	 * @return string[] List of the AI capabilities.
	 */
	private function get_capabilities(): array {
		$metadata = $this->services_api->get_service_metadata( $this->slug );
		if ( ! $metadata ) {
			return array();
		}

		return $metadata->get_capabilities();
	}

	/**
	 * Gets the available models for the service.
	 *
	 * @since 0.1.0
	 * @since 0.5.0 Return type changed to a map of model data shapes.
	 * @since n.e.x.t Return type changed to a map of model metadata objects.
	 *
	 * @return array<string, Model_Metadata> Metadata for each model, mapped by model slug.
	 */
	private function get_available_models(): array {
		if ( ! $this->services_api->is_service_available( $this->slug ) ) {
			return array();
		}

		$service = $this->services_api->get_available_service( $this->slug );
		try {
			return $service->list_models();
		} catch ( Generative_AI_Exception $e ) {
			return array();
		}
	}

	/**
	 * Checks whether the service has a forced API key (i.e. the API key option is being overridden).
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if the service has a forced API key, false otherwise.
	 */
	private function has_forced_api_key() {
		$option = sprintf( 'ais_%s_api_key', $this->slug );

		// These filters are part of get_option() and allow plugins to short-circuit the option retrieval.
		$pre = apply_filters( "pre_option_{$option}", false, $option, '' );
		$pre = apply_filters( 'pre_option', $pre, $option, '' );

		return false !== $pre;
	}
}
