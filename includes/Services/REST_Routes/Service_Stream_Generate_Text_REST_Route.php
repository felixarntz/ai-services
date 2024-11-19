<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Service_Stream_Generate_Text_REST_Route
 *
 * @since 0.3.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use Generator;
use WP_REST_Server;

/**
 * Class representing the REST API route for generating text content, streaming the response.
 *
 * @since 0.3.0
 */
class Service_Stream_Generate_Text_REST_Route extends Service_Generate_Text_REST_Route {
	const BASE    = '/services/(?P<slug>[\w-]+):stream-generate-text';
	const METHODS = WP_REST_Server::CREATABLE;

	/**
	 * Returns the route base.
	 *
	 * @since 0.3.0
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since 0.3.0
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Generates content using the given service and model.
	 *
	 * @since 0.3.0
	 *
	 * @param Generative_AI_Service          $service The service instance.
	 * @param Generative_AI_Model            $model   The model instance.
	 * @param string|Parts|Content|Content[] $content The content prompt.
	 * @return Candidates The generated content candidates.
	 *
	 * @throws REST_Exception Thrown when the model does not support text generation.
	 */
	protected function generate_content( Generative_AI_Service $service, Generative_AI_Model $model, $content ): Candidates {
		if ( ! $model instanceof With_Text_Generation ) {
			throw $this->create_missing_text_generation_exception();
		}

		$candidates_generator = $model->stream_generate_text( $content );

		$this->send_streamed_response( $candidates_generator );

		// This is just to satisfy the return type hint.
		return Candidates::from_array( array() );
	}

	/**
	 * Sends the candidates chunks in the generator as a streamed response.
	 *
	 * @since 0.3.0
	 *
	 * @param Generator<Candidates> $candidates_generator The generator yielding the candidates chunks.
	 *
	 * @SuppressWarnings(PHPMD.ErrorControlOperator)
	 */
	private function send_streamed_response( Generator $candidates_generator ): void { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
		$this->set_stream_config();

		$this->send_stream_headers();

		foreach ( $candidates_generator as $candidates ) {
			$this->send_stream_data( $candidates );
		}

		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		} elseif ( function_exists( 'litespeed_finish_request' ) ) {
			litespeed_finish_request();
		} elseif ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg', 'embed' ), true ) ) {
			while ( ob_get_level() !== 0 ) {
				ob_end_flush();
			}

			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@flush();
		}
		exit;
	}

	/**
	 * Configures the environment for streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @SuppressWarnings(PHPMD.ErrorControlOperator)
	 */
	private function set_stream_config(): void {
		// Prevent buffering on Apache.
		if ( function_exists( 'apache_setenv' ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
			@apache_setenv( 'no-gzip', '1' );
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.IniSet.Risky
		@ini_set( 'zlib.output_compression', '0' );

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.IniSet.Risky
		@ini_set( 'implicit_flush', '1' );

		while ( ob_get_level() !== 0 ) {
			ob_end_flush();
		}
		ob_implicit_flush( 1 );
	}

	/**
	 * Sends necessary headers for the streamed response.
	 *
	 * @since 0.3.0
	 */
	private function send_stream_headers(): void {
		header( 'Content-Type: text/event-stream' );
		header( 'Cache-Control: no-cache' );
		header( 'X-Accel-Buffering: no' ); // Disables FastCGI Buffering on Nginx.
		// header( 'Transfer-encoding: chunked' ); Not sure whether this is needed.
	}

	/**
	 * Sends the given candidates as a streamed data chunk.
	 *
	 * @since 0.3.0
	 *
	 * @param Candidates $candidates The candidates to send.
	 *
	 * @SuppressWarnings(PHPMD.ErrorControlOperator)
	 */
	private function send_stream_data( Candidates $candidates ): void {
		echo 'data: ' . wp_json_encode( $candidates->to_array() ) . "\n\n";

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@ob_flush();

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@flush();
	}
}
