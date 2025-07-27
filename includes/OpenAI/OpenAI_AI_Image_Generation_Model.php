<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Image_Generation_Model
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use Felix_Arntz\AI_Services\Services\Base\OpenAI_Compatible_AI_Image_Generation_Model;
use InvalidArgumentException;

/**
 * Class representing an OpenAI image generation AI model.
 *
 * @since 0.5.0
 */
class OpenAI_AI_Image_Generation_Model extends OpenAI_Compatible_AI_Image_Generation_Model {

	/**
	 * Prepares the API request parameters for generating an image.
	 *
	 * @since 0.5.0
	 *
	 * @param Content[] $contents The contents to generate an image for.
	 * @return array<string, mixed> The parameters for generating an image.
	 *
	 * @throws InvalidArgumentException Thrown if configuration values are not supported by the model.
	 */
	protected function prepare_generate_image_params( array $contents ): array {
		$params = parent::prepare_generate_image_params( $contents );

		/*
		 * For the gpt-image-* models, an optional 'output_format' parameter can be set to receive a different image
		 * MIME type than the default 'iamge/png'.
		 * At the same time, the 'response_format' parameter is not supported by these models, as they will always
		 * return base64-encoded data.
		 */
		if ( str_starts_with( $this->get_model_slug(), 'gpt-image-' ) ) {
			$generation_config = $this->get_image_generation_config();

			if ( $generation_config ) {
				$response_mime_type = $generation_config->get_response_mime_type();
				if ( $response_mime_type ) {
					$params['output_format'] = preg_replace( '/^image\//', '', $response_mime_type );
				}
			}

			unset( $params['response_format'] );
		}

		return $params;
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	protected function get_generation_config_transformers(): array {
		$transformers = parent::get_generation_config_transformers();

		$is_gpt = str_starts_with( $this->get_model_slug(), 'gpt-image-' );

		// The 'size' parameter is OpenAI API specific, and not commonly supported by other OpenAI compatible APIs.
		$transformers['size'] = static function ( Image_Generation_Config $config ) use ( $is_gpt ) {
			$aspect_ratio = $config->get_aspect_ratio();
			$larger_side  = $is_gpt ? '1536' : '1792';
			switch ( $aspect_ratio ) {
				case '1:1':
					return '1024x1024';
				// Unfortunately, each model only supports either 16:9 or 4:3, not both.
				case '16:9':
				case '4:3':
					return "{$larger_side}x1024";
				// Unfortunately, each model only supports either 9:16 or 3:4, not both.
				case '9:16':
				case '3:4':
					return "1024x{$larger_side}";
			}
			return '';
		};

		return $transformers;
	}
}
