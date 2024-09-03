/**
 * External dependencies
 */
import Chatbot from 'react-chatbot-kit';
import 'react-chatbot-kit/build/main.css';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import config from '../../config';
import MessageParser from '../MessageParser';
import ActionProvider from '../ActionProvider';
import './style.scss';

/**
 * Renders the chatbot.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function ChatbotApp() {
	const [ isVisible, setIsVisible ] = useState( false );
	const toggleVisibility = () => setIsVisible( ! isVisible );

	return (
		<>
			{ isVisible && (
				<div
					id="wp-starter-plugin-chatbot-container"
					className="chatbot-container"
				>
					<Chatbot
						config={ config }
						messageParser={ MessageParser }
						actionProvider={ ActionProvider }
					/>
				</div>
			) }
			<Button
				variant="primary"
				onClick={ toggleVisibility }
				className="chatbot-button"
				aria-controls="wp-starter-plugin-chatbot-container"
				aria-expanded={ isVisible }
			>
				{ __( 'Need help?', 'wp-starter-plugin' ) }
			</Button>
		</>
	);
}
