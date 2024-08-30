<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Types\Chat_Session
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Types;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\With_Text_Generation;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Util\Formatter;

/**
 * Class representing a chat session with a generative model
 *
 * @since n.e.x.t
 */
final class Chat_Session {

	/**
	 * The generative AI model with support for text generation.
	 *
	 * @since n.e.x.t
	 * @var With_Text_Generation
	 */
	private $model;

	/**
	 * The chat history.
	 *
	 * @since n.e.x.t
	 * @var Content[]
	 */
	private $history;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
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
	 * @since n.e.x.t
	 *
	 * @return Content[] The chat history.
	 */
	public function get_history(): array {
		return $this->history;
	}

	/**
	 * Sends a chat message to the model.
	 *
	 * @since n.e.x.t
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
				esc_html__( 'The response did not include any relevant candidates.', 'wp-starter-plugin' )
			);
		}

		$response_content = $candidates->get( 0 )->get_content();

		$this->history[] = $new_content;
		$this->history[] = $response_content;

		return $response_content;
	}

	/**
	 * Validates the chat history.
	 *
	 * @since n.e.x.t
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
					esc_html__( 'The history must contain Content instances.', 'wp-starter-plugin' )
				);
			}

			if ( $first && Content::ROLE_USER !== $content->get_role() ) {
				throw new InvalidArgumentException(
					esc_html__( 'The first Content instance in the history must be user content.', 'wp-starter-plugin' )
				);
			}

			if ( $content->get_parts()->count() < 1 ) {
				throw new InvalidArgumentException(
					esc_html__( 'Each Content instance must have at least one part.', 'wp-starter-plugin' )
				);
			}

			$first = false;
		}
	}
}
