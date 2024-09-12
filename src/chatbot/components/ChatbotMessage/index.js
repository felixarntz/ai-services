/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';
import { store as aiStore } from '@wp-starter-plugin/ai-store';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useChatIdContext } from '../../context';
import './style.scss';

/**
 * Renders the chatbot message.
 *
 * @since n.e.x.t
 *
 * @param {Object} props         The component props.
 * @param {string} props.message The message string.
 * @param {Node}   props.loader  The loader to potentially render.
 * @return {Component} The component to be rendered.
 */
export default function ChatbotMessage( { message, loader } ) {
	const chatBoxCustomStyles = { backgroundColor: '' };
	const arrowCustomStyles = { borderRightColor: '' };
	const withAvatar = true;

	// TODO: How can we know whether the chatbot is loading (via 'react-chatbot-kit')?
	const chatId = useChatIdContext();
	const loading = useSelect(
		( select ) => ! message && select( aiStore ).isChatLoading( chatId )
	);

	const messageData = useMemo( () => {
		const parts = message.split( '---' );
		if ( parts.length !== 3 ) {
			return {
				message: parts[ 0 ].trim(),
				linkUrl: '',
				linkText: '',
			};
		}
		return {
			message: parts[ 0 ].trim(),
			linkUrl: parts[ 1 ].trim(),
			linkText: parts[ 2 ].trim(),
		};
	}, [ message ] );

	/*
	 * Effectively the same layout as the regular ChatbotMessage component from "react-chatbot-kit",
	 * as much as possible.
	 */
	return (
		<div
			className="react-chatbot-kit-chat-bot-message"
			style={ chatBoxCustomStyles }
		>
			{ loading && loader }
			{ ! loading && (
				<>
					<Markdown
						options={ { forceBlock: true, forceWrapper: true } }
					>
						{ messageData.message }
					</Markdown>
					{ messageData.linkUrl && messageData.linkText && (
						// Don't use the Button component so that we don't need to load the heavy 'wp-components' stylesheet everywhere.
						<a
							className="button button-secondary"
							href={ messageData.linkUrl }
						>
							{ messageData.linkText }
						</a>
					) }
				</>
			) }
			{ withAvatar && (
				<div
					className="react-chatbot-kit-chat-bot-message-arrow"
					style={ arrowCustomStyles }
				></div>
			) }
		</div>
	);
}
