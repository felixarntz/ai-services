<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\AI_Capabilities
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use InvalidArgumentException;

/**
 * Class exposing the available AI capabilities and related static utility methods.
 *
 * @since 0.1.0
 */
final class AI_Capabilities {
	const CAPABILITY_IMAGE_GENERATION = 'image_generation';
	const CAPABILITY_MULTIMODAL_INPUT = 'multimodal_input';
	const CAPABILITY_TEXT_GENERATION  = 'text_generation';

	/**
	 * Gets the AI capabilities that the given model class supports.
	 *
	 * @since 0.1.0
	 *
	 * @param string $model_class The model class name.
	 * @return string[] The AI capabilities that the model class supports, based on the interfaces it implements.
	 */
	public static function get_model_class_capabilities( string $model_class ): array {
		$interfaces = class_implements( $model_class );

		$capabilities = array();
		if ( isset( $interfaces[ With_Image_Generation::class ] ) ) {
			$capabilities[] = self::CAPABILITY_IMAGE_GENERATION;
		}
		if ( isset( $interfaces[ With_Multimodal_Input::class ] ) ) {
			$capabilities[] = self::CAPABILITY_MULTIMODAL_INPUT;
		}
		if ( isset( $interfaces[ With_Text_Generation::class ] ) ) {
			$capabilities[] = self::CAPABILITY_TEXT_GENERATION;
		}
		return $capabilities;
	}

	/**
	 * Gets the model slugs that satisfy the given capabilities.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, string[]> $models       Map of the available model slugs and their capabilities.
	 * @param string[]                $capabilities The required capabilities that the models should satisfy.
	 * @return string[] Slugs of all models that satisfy the given capabilities.
	 *
	 * @throws InvalidArgumentException Thrown if no model satisfies the given capabilities.
	 */
	public static function get_model_slugs_for_capabilities( array $models, array $capabilities ): array {
		$model_slugs = array();
		foreach ( $models as $model_slug => $model_caps ) {
			if ( ! array_diff( $capabilities, $model_caps ) ) {
				$model_slugs[] = $model_slug;
			}
		}

		if ( ! $model_slugs ) {
			throw new InvalidArgumentException(
				esc_html__( 'No model satisfies the given capabilities.', 'ai-services' )
			);
		}

		return $model_slugs;
	}
}
