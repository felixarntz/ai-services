<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Contracts;

use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidate;
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
	 * Gets the model name.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The model name.
	 */
	public function get_model_name(): string;

	/**
	 * Generates content using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Content[] $content         Prompt for the content to generate. Optionally, an array
	 *                                                        can be passed for additional context (e.g. chat history).
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidate[] The response candidates with generated content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function generate_content( $content, array $request_options = array() ): array;

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
