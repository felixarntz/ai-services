<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot_AI
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin\Gemini\Gemini_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidate;

/**
 * Class for the AI configuration powering the chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot_AI {

	/**
	 * The AI instance.
	 *
	 * @since n.e.x.t
	 * @var Gemini_AI_Service
	 */
	private $ai;

	/**
	 * The generative model.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_Model|null
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Gemini_AI_Service $ai The AI instance.
	 */
	public function __construct( Gemini_AI_Service $ai ) {
		$this->ai = $ai;
	}

	/**
	 * Get the generative model for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generative_AI_Model The generative model.
	 */
	public function get_model(): Generative_AI_Model {
		if ( null === $this->model ) {
			$this->model = $this->ai->get_model(
				array(
					'model'              => 'gemini-1.5-flash',
					'system_instruction' => $this->get_system_instruction(),
				)
			);
		}
		return $this->model;
	}

	/**
	 * Gets the text from the given response of the generative model.
	 *
	 * The first candidate in the response is used, and its text parts are concatenated.
	 *
	 * @since n.e.x.t
	 *
	 * @param Candidate[] $candidates The response from the generative model.
	 * @return string The text.
	 *
	 * @throws Generative_AI_Exception If the response does not include any text parts.
	 */
	public function get_text_from_candidates( array $candidates ): string {
		$content   = $candidates[0]->get_content();
		$parts     = $content->get_parts();
		$num_parts = $parts->count();
		if ( ! $num_parts ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the AI service does not include any parts.', 'wp-starter-plugin' )
			);
		}

		$text_parts = array();
		for ( $i = 0; $i < $num_parts; $i++ ) {
			$part = $parts->get( $i );
			if ( isset( $part['text'] ) ) {
				$text_parts[] = trim( $part['text'] );
			}
		}

		if ( ! $text_parts ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the AI service does not include any text parts.', 'wp-starter-plugin' )
			);
		}

		return implode( "\n\n", $text_parts );
	}

	/**
	 * Get the system instruction for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The system instruction.
	 */
	private function get_system_instruction(): string {
		$instruction = 'You are a chatbot running inside a WordPress site.
You are here to help users with their questions and provide information.
You can also provide assistance with troubleshooting and technical issues.
The WordPress site URL is ' . home_url( '/' ) . ' and the URL to the admin interface is ' . admin_url( '/' ) . ".
You may also provide links to relevant sections of the WordPress admin interface, contextually for the site.
Any links provided must not be contained within the message text itself, but separately at the very end of the message.
The link must be separated from the message text by three hyphens (---).
For example: 'You can edit posts in the Posts screen. --- " . admin_url( 'edit.php' ) . "'.
Here is some additional information about the WordPress site, so that you can help users more effectively:
 - The site is running on WordPress version " . get_bloginfo( 'version' ) . '.
 - The primary locale of the site is ' . get_locale() . '.
 - The site is using the ' . get_template() . ' theme.
';

		if ( wp_is_block_theme() ) {
			$instruction .= ' - The theme is a block theme.';
		} else {
			$instruction .= ' - The theme is a classic theme.';
		}

		return $instruction;
	}
}
