<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\With_Chat_History_Trait
 *
 * @since 0.3.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Chat_Session;
use Felix_Arntz\AI_Services\Services\API\Types\Content;

/**
 * Trait for a model which implements the With_Chat_History interface.
 *
 * @since 0.3.0
 */
trait With_Chat_History_Trait {

	/**
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[] $history Optional. The chat history. Default empty array.
	 * @return Chat_Session The chat session.
	 */
	final public function start_chat( array $history = array() ): Chat_Session {
		return new Chat_Session( $this, $history );
	}
}
