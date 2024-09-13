<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Types\Candidates;
use Felix_Arntz\AI_Services\Services\Types\Chat_Session;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Parts;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use InvalidArgumentException;

/**
 * Trait for a model which implements the With_Text_Generation interface.
 *
 * @since n.e.x.t
 */
trait With_Text_Generation_Trait {

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
	 */
	final public function generate_text( $content, array $request_options = array() ): Candidates {
		if ( is_array( $content ) ) {
			$contents = array_map(
				array( Formatter::class, 'format_new_content' ),
				$content
			);
		} else {
			$contents = array( Formatter::format_new_content( $content ) );
		}

		if ( Content::ROLE_USER !== $contents[0]->get_role() ) {
			throw new InvalidArgumentException(
				esc_html__( 'The first Content instance in the conversation or prompt must be user content.', 'ai-services' )
			);
		}

		return $this->send_generate_text_request( $contents, $request_options );
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
	 * Sends a request to generate text content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	abstract protected function send_generate_text_request( array $contents, array $request_options ): Candidates;
}
