<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Abstract_Generative_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services;

use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Candidate;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Chat_Session;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Content;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Parts;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Util\Formatter;

/**
 * Base class for a generative AI model.
 *
 * @since n.e.x.t
 */
abstract class Abstract_Generative_AI_Model implements Generative_AI_Model {

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
	final public function generate_content( $content, array $request_options = array() ): array {
		if ( is_array( $content ) ) {
			$contents = array_map(
				array( Formatter::class, 'format_new_content' ),
				$content
			);
		} else {
			$contents = array( Formatter::format_new_content( $content ) );
		}

		return $this->send_generate_content_request( $contents, $request_options );
	}

	/**
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $history Optional. The chat history. Default empty array.
	 * @return Chat_Session The chat session.
	 */
	final public function start_chat( array $history = array() ): Chat_Session {
		return new Chat_Session( $this, $history );
	}

	/**
	 * Sends a request to generate content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidate[] The response candidates with generated content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	abstract protected function send_generate_content_request( array $contents, array $request_options ): array;
}
