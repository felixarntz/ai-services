<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\Transformer
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use InvalidArgumentException;

/**
 * Class providing static methods for transforming data.
 *
 * @since 0.2.0
 */
final class Transformer {

	/**
	 * Transforms the given content using the provided transformers.
	 *
	 * @since 0.2.0
	 *
	 * @param Content                 $content       The content to transform.
	 * @param array<string, callable> $transformers  The transformers to use. Each transformer callback should accept
	 *                                               the content as its only parameter and return the transformed value
	 *                                               for its key.
	 * @return array<string, mixed> The transformed content.
	 *
	 * @throws InvalidArgumentException Thrown if a provided transformer is not callable.
	 */
	public static function transform_content( Content $content, array $transformers ): array {
		$data = array();

		foreach ( $transformers as $key => $transformer ) {
			if ( ! is_callable( $transformer ) ) {
				throw new InvalidArgumentException(
					sprintf(
						'The transformer for key %s is invalid.',
						htmlspecialchars( $key ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					)
				);
			}

			// Transform the value and set it if truthy.
			$value = $transformer( $content );
			if ( ! $value ) {
				continue;
			}
			$data[ $key ] = $value;
		}

		return $data;
	}

	/**
	 * Merges the given Generation_Config instance into the given parameters using the provided transformers.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed>    $params       The parameters to merge the generation config into.
	 * @param Generation_Config       $config       The generation config to use for the transformation.
	 * @param array<string, callable> $transformers The transformers to use. Each transformer callback should accept
	 *                                              the generation config as its only parameter and return the
	 *                                              transformed value for its key.
	 * @return array<string, mixed> The transformed parameters.
	 *
	 * @throws InvalidArgumentException Thrown if a provided transformer is not callable.
	 */
	public static function transform_generation_config_params( array $params, Generation_Config $config, array $transformers ): array {
		foreach ( $transformers as $key => $transformer ) {
			if ( ! is_callable( $transformer ) ) {
				throw new InvalidArgumentException(
					sprintf(
						'The transformer for key %s is invalid.',
						htmlspecialchars( $key ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					)
				);
			}

			// Already set parameters take precedence.
			if ( isset( $params[ $key ] ) ) {
				continue;
			}

			// Transform the value and set it if truthy.
			$value = $transformer( $config );
			if ( ! $value ) {
				continue;
			}
			$params[ $key ] = $value;
		}

		return $params;
	}
}
