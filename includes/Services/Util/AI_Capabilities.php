<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\AI_Capabilities
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;

/**
 * Class exposing the available AI capabilities and related static utility methods.
 *
 * @since 0.1.0
 */
final class AI_Capabilities {
	const CAPABILITY_TEXT_GENERATION  = 'text_generation';
	const CAPABILITY_IMAGE_GENERATION = 'image_generation';

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
		if ( isset( $interfaces[ With_Text_Generation::class ] ) ) {
			$capabilities[] = self::CAPABILITY_TEXT_GENERATION;
		}
		if ( isset( $interfaces[ With_Image_Generation::class ] ) ) {
			$capabilities[] = self::CAPABILITY_IMAGE_GENERATION;
		}
		return $capabilities;
	}
}
