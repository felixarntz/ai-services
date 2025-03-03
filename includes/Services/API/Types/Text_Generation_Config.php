<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use InvalidArgumentException;

/**
 * Class representing text configuration options for a generative AI model.
 *
 * @since 0.2.0
 * @since 0.5.0 Renamed from `Generation_Config`.
 */
class Text_Generation_Config extends Generation_Config {

	/**
	 * Creates a Generation_Config instance from an array of content data.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Generation_Config Generation_Config instance.
	 *
	 * @phpstan-return Text_Generation_Config
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Generation_Config {
		return new Text_Generation_Config( $data );
	}
}
