<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\AI_Capabilities
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Function_Calling;
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
		if ( isset( $interfaces[ With_Chat_History::class ] ) ) {
			$capabilities[] = AI_Capability::CHAT_HISTORY;
		}
		if ( isset( $interfaces[ With_Function_Calling::class ] ) ) {
			$capabilities[] = AI_Capability::FUNCTION_CALLING;
		}
		if ( isset( $interfaces[ With_Image_Generation::class ] ) ) {
			$capabilities[] = AI_Capability::IMAGE_GENERATION;
		}
		if ( isset( $interfaces[ With_Multimodal_Input::class ] ) ) {
			$capabilities[] = AI_Capability::MULTIMODAL_INPUT;
		}
		if ( isset( $interfaces[ With_Text_Generation::class ] ) ) {
			$capabilities[] = AI_Capability::TEXT_GENERATION;
		}
		return $capabilities;
	}

	/**
	 * Gets the AI capabilities that the given model instance supports.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_Model $model The model instance.
	 * @return string[] The AI capabilities that the model instance supports, based on the interfaces it implements.
	 */
	public static function get_model_instance_capabilities( Generative_AI_Model $model ): array {
		$capabilities = array();
		if ( $model instanceof With_Chat_History ) {
			$capabilities[] = AI_Capability::CHAT_HISTORY;
		}
		if ( $model instanceof With_Function_Calling ) {
			$capabilities[] = AI_Capability::FUNCTION_CALLING;
		}
		if ( $model instanceof With_Image_Generation ) {
			$capabilities[] = AI_Capability::IMAGE_GENERATION;
		}
		if ( $model instanceof With_Multimodal_Input ) {
			$capabilities[] = AI_Capability::MULTIMODAL_INPUT;
		}
		if ( $model instanceof With_Text_Generation ) {
			$capabilities[] = AI_Capability::TEXT_GENERATION;
		}
		return $capabilities;
	}

	/**
	 * Gets the model slugs that satisfy the given capabilities.
	 *
	 * @since 0.1.0
	 * @since n.e.x.t Now expects an array of model data shapes, mapped by model slug.
	 *
	 * @param array<string, array{slug: string, name: string, capabilities: string[]}> $models       Data for each
	 *                                                                                               model, mapped by
	 *                                                                                               model slug.
	 * @param string[]                                                                 $capabilities The required
	 *                                                                                               capabilities that
	 *                                                                                               the models should
	 *                                                                                               satisfy.
	 * @return string[] Slugs of all models that satisfy the given capabilities.
	 *
	 * @throws InvalidArgumentException Thrown if no model satisfies the given capabilities.
	 */
	public static function get_model_slugs_for_capabilities( array $models, array $capabilities ): array {
		$model_slugs = array();
		foreach ( $models as $model_slug => $model_data ) {
			if ( ! array_diff( $capabilities, $model_data['capabilities'] ) ) {
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
