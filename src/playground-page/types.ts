/**
 * External dependencies
 */
import type {
	AiCapability,
	Modality,
	Content,
	ModelParams,
	Candidates,
} from '@ai-services/ai/types';

// Stubs for the WordPress attachment type.
type WordPressAttachmentSizeData = {
	url: string;
	width: number;
	height: number;
};
export type WordPressAttachment = {
	id: number;
	url: string;
	mime: string;
	icon: string;
	sizes?: Record< string, WordPressAttachmentSizeData >;
	[ key: string ]: unknown;
};

// Compatible with broader `HistoryEntry` type from '@ai-services/ai/types'.
export type AiPlaygroundMessage = {
	content: Content;
	type: 'user' | 'model' | 'error';
} & AiPlaygroundMessageAdditionalData;

export type AiPlaygroundMessageAdditionalData = {
	service?: {
		slug: string;
		name: string;
	};
	model?: {
		slug: string;
		name: string;
	};
	foundationalCapability?: AiCapability;
	attachment?: WordPressAttachment;
	attachments?: ( WordPressAttachment | null )[];
	rawData?:
		| {
				content: Content | Content[];
				modelParams: ModelParams;
		  }
		| Candidates;
};

export type AiServiceOption = {
	identifier: string;
	label: string;
};

export type AiModelOption = {
	identifier: string;
	label: string;
};

export type AiCapabilityOption = {
	identifier: AiCapability;
	label: string;
};

export type ModalityOption = {
	identifier: Modality;
	label: string;
};
