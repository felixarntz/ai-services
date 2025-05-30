<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Chat_Session
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Generator;
use InvalidArgumentException;

/**
 * Class representing a chat session with a generative model.
 *
 * @since 0.1.0
 */
final class Chat_Session {

	/**
	 * The generative AI model with support for text generation.
	 *
	 * @since 0.1.0
	 * @var With_Text_Generation
	 */
	private $model;

	/**
	 * The chat history.
	 *
	 * @since 0.1.0
	 * @var Content[]
	 */
	private $history;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param With_Text_Generation $model   The generative AI model with support for text generation.
	 * @param Content[]            $history Optional. The chat history. Default empty array.
	 */
	public function __construct( With_Text_Generation $model, array $history = array() ) {
		$this->model = $model;

		$this->validate_history( $history );
		$this->history = $history;
	}

	/**
	 * Gets the chat history.
	 *
	 * @since 0.1.0
	 *
	 * @return Content[] The chat history.
	 */
	public function get_history(): array {
		return $this->history;
	}

	/**
	 * Sends a chat message to the model.
	 *
	 * @since 0.1.0
	 *
	 * @param string|Parts|Content $content         The message to send.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Content The response content.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function send_message( $content, array $request_options = array() ): Content {
		$new_content = Formatter::format_new_content( $content );

		$contents   = $this->history;
		$contents[] = $new_content;

		$candidate_filter_args = array();
		if ( isset( $request_options['candidate_filter_args'] ) ) {
			$candidate_filter_args = $request_options['candidate_filter_args'];
			unset( $request_options['candidate_filter_args'] );
		}

		$candidates = $this->model->generate_text( $contents, $request_options );
		if ( $candidate_filter_args ) {
			$candidates = $candidates->filter( $candidate_filter_args );
		}

		if ( count( $candidates ) === 0 ) {
			throw new Generative_AI_Exception(
				'The response did not include any relevant candidates.'
			);
		}

		$response_contents = Helpers::get_candidate_contents( $candidates );
		$response_content  = $response_contents[0];

		$this->history[] = $new_content;
		$this->history[] = $response_content;

		return $response_content;
	}

	/**
	 * Sends a chat message to the model, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param string|Parts|Content $content         The message to send.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generator<Content> Generator that yields the chunks of content with generated text.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function stream_send_message( $content, array $request_options = array() ): Generator {
		$new_content = Formatter::format_new_content( $content );

		$contents   = $this->history;
		$contents[] = $new_content;

		$candidate_filter_args = array();
		if ( isset( $request_options['candidate_filter_args'] ) ) {
			$candidate_filter_args = $request_options['candidate_filter_args'];
			unset( $request_options['candidate_filter_args'] );
		}

		$candidates_generator = $this->model->stream_generate_text( $contents, $request_options );

		$candidates_processor = Helpers::process_candidates_stream( $candidates_generator );
		foreach ( $candidates_generator as $candidates ) {
			if ( $candidate_filter_args ) {
				$candidates = $candidates->filter( $candidate_filter_args );
			}

			if ( count( $candidates ) === 0 ) {
				throw new Generative_AI_Exception(
					'The response did not include any relevant candidates.'
				);
			}

			$candidates_processor->add_chunk( $candidates );

			$partial_contents = Helpers::get_candidate_contents( $candidates );
			$partial_content  = $partial_contents[0];

			yield $partial_content;
		}

		$complete_candidates = $candidates_processor->get_complete();

		$complete_contents = Helpers::get_candidate_contents( $complete_candidates );
		$complete_content  = $complete_contents[0];

		$this->history[] = $new_content;
		$this->history[] = $complete_content;
	}

	/**
	 * Validates the chat history.
	 *
	 * @since 0.1.0
	 *
	 * @param Content[] $history The chat history.
	 *
	 * @throws InvalidArgumentException Thrown if the history is invalid.
	 */
	private function validate_history( array $history ): void {
		$first = true;
		foreach ( $history as $content ) {
			if ( ! $content instanceof Content ) {
				throw new InvalidArgumentException(
					'The history must contain Content instances.'
				);
			}

			if ( $first && Content_Role::USER !== $content->get_role() ) {
				throw new InvalidArgumentException(
					'The first Content instance in the history must be user content.'
				);
			}

			if ( $content->get_parts()->count() < 1 ) {
				throw new InvalidArgumentException(
					'Each Content instance must have at least one part.'
				);
			}

			$first = false;
		}
	}
}
