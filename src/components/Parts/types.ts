/**
 * External dependencies
 */
import type { Part } from '@ai-services/ai/types';

export type MediaProps = {
	mimeType: string;
	src: string;
};

export type JsonTextareaProps = {
	data: unknown;
	label: string;
};

export type PartsProps = {
	parts: Part[];
};
