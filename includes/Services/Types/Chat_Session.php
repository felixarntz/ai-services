<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Types\Chat_Session
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Types;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Util\Formatter;

/**
 * Class representing a chat session with a generative model
 *
 * @since n.e.x.t
 */
final class Chat_Session {

	/**
	 * The generative AI model.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_Model
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
	 * @param Generative_AI_Model $model   The generative AI model.
	 * @param Content[]           $history Optional. The chat history. Default empty array.
	 */
	public function __construct( Generative_AI_Model $model, array $history = array() ) {
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
	 * @return Candidate[] The response candidates with generated content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function send_message( $content, array $request_options = array() ): array {
		$new_content = Formatter::format_new_content( $content );

		$contents   = $this->history;
		$contents[] = $new_content;

		$candidates = $this->model->generate_content( $contents, $request_options );

		$this->history[] = $new_content;
		$this->history[] = $candidates[0]->get_content();

		return $candidates;
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

			if ( $first && 'user' !== $content->get_role() ) {
				throw new InvalidArgumentException(
					esc_html__( 'The first Content instance in the history must be user content.', 'wp-starter-plugin' )
				);
			}

			if ( ! $this->is_valid_role( $content->get_role() ) ) {
				throw new InvalidArgumentException(
					esc_html(
						sprintf(
							/* translators: %s: invalid role encountered */
							__( 'The role %s is invalid.', 'wp-starter-plugin' ),
							$content->get_role()
						)
					)
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

	/**
	 * Checks if the given role is valid.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $role The role to check.
	 * @return bool True if the role is valid, false otherwise.
	 */
	private function is_valid_role( string $role ): bool {
		return in_array( $role, array( 'user', 'model', 'function', 'system' ), true );
	}
}
