<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\OpenAI_Compatible_Image_Generation_With_Multimodal_Input_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;

/**
 * Trait for an OpenAI compatible image generation model which implements multimodal input.
 *
 * @since n.e.x.t
 */
trait OpenAI_Compatible_Image_Generation_With_Multimodal_Input_Trait {

	/**
	 * Gets the API route for generating an image.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $contents The contents to generate an image for.
	 * @return string The route for generating an image.
	 */
	protected function get_generate_image_route( array $contents ): string {
		/*
		 * The OpenAI API specification only allows a single prompt, as a text string.
		 * For this reason, we only use the last content in case multiple messages are provided, and we prepend the
		 * system instruction if set.
		 */
		$last_content = end( $contents );
		foreach ( $last_content->get_parts() as $part ) {
			if ( $part instanceof Inline_Data_Part || $part instanceof File_Data_Part ) {
				// If there is an image present, we need to use the edit route.
				return 'images/edits';
			}
		}
		return parent::get_generate_image_route( $contents );
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The content transformers.
	 */
	protected function get_content_transformers(): array {
		$transformers = parent::get_content_transformers();

		$transformers['image'] = function ( Content $content ) {
			$input_images = array();

			foreach ( $content->get_parts() as $part ) {
				if ( ! $part instanceof Inline_Data_Part && ! $part instanceof File_Data_Part ) {
					continue;
				}

				$mime_type = $part->get_mime_type();
				if ( ! str_starts_with( $mime_type, 'image/' ) ) {
					throw $this->get_api_client()->create_bad_request_exception(
						'The API only supports text and image parts as image generation input.'
					);
				}

				if ( $part instanceof File_Data_Part ) {
					$input_images[] = $part->get_file_uri();
				} else {
					$input_images[] = $part->get_base64_data();
				}
			}

			return $input_images;
		};

		return $transformers;
	}
}
