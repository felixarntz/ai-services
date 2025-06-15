/**
 * External dependencies
 */
import type { Content } from '@ai-services/ai/types';
import type { ComponentType } from 'react';

export type ChatbotConfig = {
	chatId: string;
	labels: {
		title: string;
		subtitle: string;
		closeButton: string;
		sendButton: string;
		inputLabel: string;
		inputPlaceholder: string;
	};
	initialBotMessage?: string;
	getErrorChatResponse?: ( error: unknown ) => string;
	ResponseRenderer?: ComponentType< ResponseRendererProps >;
	useStreaming?: boolean;
	renderStreamResponse?: ( text: string ) => string;
};

export type ChatbotMessage = Content & {
	type?: 'error';
};

export type ResponseRendererProps = {
	text: string;
};

export type AsyncContentGenerator = AsyncGenerator< Content, void, void >;
