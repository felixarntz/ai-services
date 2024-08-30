<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Contracts;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidates;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Chat_Session;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Content;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts;

/**
 * Interface for a class representing a generative AI model.
 *
 * @since n.e.x.t
 */
interface Generative_AI_Model {

	/**
	 * Gets the model slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The model slug.
	 */
	public function get_model_slug(): string;

	/**
	 * Generates text content using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Content[] $content         Prompt for the content to generate. Optionally, an array
	 *                                                        can be passed for additional context (e.g. chat history).
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws InvalidArgumentException Thrown if the given content is invalid.
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function generate_text( $content, array $request_options = array() ): Candidates;

	/**
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $history Optional. The chat history. Default empty array.
	 * @return Chat_Session The chat session.
	 */
	public function start_chat( array $history = array() ): Chat_Session;
}
