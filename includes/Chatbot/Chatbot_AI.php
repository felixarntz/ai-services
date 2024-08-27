<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot_AI
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidates;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts\Text_Part;

/**
 * Class for the AI configuration powering the chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot_AI {

	/**
	 * The AI service instance.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_Service
	 */
	private $service;

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
	 * @param Generative_AI_Service $service The AI instance.
	 */
	public function __construct( Generative_AI_Service $service ) {
		$this->service = $service;
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
			// TODO: Support services other than Google.
			$this->model = $this->service->get_model(
				'gemini-1.5-flash',
				array( 'system_instruction' => $this->get_system_instruction() )
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
	 * @param Candidates $candidates The candidates response from the generative model.
	 * @return string The text.
	 *
	 * @throws Generative_AI_Exception If the response does not include any text parts.
	 */
	public function get_text_from_candidates( Candidates $candidates ): string {
		$candidates = $candidates->filter( array( 'part_class_name' => Text_Part::class ) );
		if ( count( $candidates ) === 0 ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the AI service does not include any text parts.', 'wp-starter-plugin' )
			);
		}

		$parts = $candidates->get( 0 )->get_content()->get_parts();

		$text_parts = array();
		foreach ( $parts as $part ) {
			$text_parts[] = trim( $part->to_array()['text'] );
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
