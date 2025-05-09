<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History
 *
 * @since 0.3.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\API\Types\Chat_Session;
use Felix_Arntz\AI_Services\Services\API\Types\Content;

/**
 * Interface for a model which allows chat history, i.e. text generation with multiple chat turns as prompt.
 *
 * @since 0.3.0
 */
interface With_Chat_History extends With_Text_Generation {

	/**
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[] $history Optional. The chat history. Default empty array.
	 * @return Chat_Session The chat session.
	 */
	public function start_chat( array $history = array() ): Chat_Session;
}
