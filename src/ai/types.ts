/**
 * Internal dependencies
 */
import { _VALUE_MAP as _AI_CAPABILITY_VALUE_MAP } from './enums/ai-capability';
import {
	_VALUE_MAP as _CONTENT_ROLE_VALUE_MAP,
	SYSTEM,
} from './enums/content-role';
import { _VALUE_MAP as _MODALITY_VALUE_MAP } from './enums/modality';
import { _VALUE_MAP as _SERVICE_TYPE_VALUE_MAP } from './enums/service-type';

/*
 * Enums.
 * ================================================
 */

export type AiCapability = keyof typeof _AI_CAPABILITY_VALUE_MAP;
export type ContentRole = keyof typeof _CONTENT_ROLE_VALUE_MAP;
export type Modality = keyof typeof _MODALITY_VALUE_MAP;
export type ServiceType = keyof typeof _SERVICE_TYPE_VALUE_MAP;

/*
 * Content and parts.
 * ================================================
 */

export type Content< Role = ContentRole > = {
	role: Role;
	parts: Part[];
};

export type TextPart = {
	text: string;
};

export type InlineDataPart = {
	inlineData: {
		mimeType: string;
		data: string;
	};
};

export type FileDataPart = {
	fileData: {
		mimeType: string;
		fileUri: string;
	};
};

export type FunctionCallPart = {
	functionCall:
		| {
				id?: string;
				name: string;
				args: Record< string, unknown >;
		  }
		| {
				id: string;
				name?: string;
				args: Record< string, unknown >;
		  };
};

export type FunctionResponsePart = {
	functionResponse:
		| {
				id?: string;
				name: string;
				response: unknown;
		  }
		| {
				id: string;
				name?: string;
				response: unknown;
		  };
};

export type Part =
	| TextPart
	| InlineDataPart
	| FileDataPart
	| FunctionCallPart
	| FunctionResponsePart;

/*
 * Candidates.
 * ================================================
 */

export type Candidate = {
	content: Content;
	[ key: string ]: unknown;
};

export type Candidates = Candidate[];

export type AsyncCandidatesGenerator = AsyncGenerator<
	Candidate[],
	void,
	void
>;

/*
 * Chat session.
 * ================================================
 */

export type ChatSessionOptions = {
	history?: Content[];
};

export type ChatConfigOptions = {
	service?: string;
	modelParams: ModelParams;
};

export type StartChatOptions = ChatConfigOptions & ChatSessionOptions;

/*
 * History.
 * ================================================
 */

export type HistoryEntry = {
	content: Content;
	[ key: string ]: unknown;
};

export type History = {
	feature: string;
	slug: string;
	lastUpdated: string;
	entries: HistoryEntry[];
};

/*
 * Model params.
 * ================================================
 */

export type ModelParams = {
	feature: string;
	model?: string;
	capabilities?: AiCapability[];
	tools?: Tool[];
	toolConfig?: ToolConfig;
	generationConfig?: TextGenerationConfig | ImageGenerationConfig;
	systemInstruction?: SystemInstruction;
};

export type FunctionDeclaration = {
	name: string;
	description?: string;
	parameters?: Record< string, unknown >;
};

export type FunctionDeclarationsTool = {
	functionDeclarations: FunctionDeclaration[];
};

export type WebSearchTool = {
	webSearch: {
		allowedDomains?: string[];
		disallowedDomains?: string[];
	};
};

export type Tool = FunctionDeclarationsTool | WebSearchTool;

export type ToolConfig = {
	functionCallMode?: 'auto' | 'any';
	allowedFunctionNames?: string[];
};

export type TextGenerationConfig = {
	stopSequences?: string[];
	responseMimeType?: 'text/plain' | 'application/json';
	responseSchema?: Record< string, unknown >;
	candidateCount?: number;
	maxOutputTokens?: number;
	temperature?: number;
	topP?: number;
	topK?: number;
	presencePenalty?: number;
	frequencyPenalty?: number;
	responseLogprobs?: boolean;
	logprobs?: number;
	outputModalities?: Modality[];
	[ key: string ]: unknown;
};

export type ImageGenerationConfig = {
	responseMimeType?: 'image/png' | 'image/jpeg' | 'image/webp';
	candidateCount?: number;
	aspectRatio?: '1:1' | '16:9' | '9:16' | '4:3' | '3:4';
	responseType?: 'inline_data' | 'file_data';
	[ key: string ]: unknown;
};

export type SystemInstruction = Content< typeof SYSTEM >;

/*
 * Service and model definition.
 * ================================================
 */

export type ServiceResource = {
	slug: string;
	metadata: ServiceMetadata;
	is_available: boolean;
	available_models: Record< string, ModelMetadata >;
	has_forced_api_key: boolean;
};

export type ServiceMetadata = {
	slug: string;
	name: string;
	credentials_url: string;
	type: ServiceType;
	capabilities: AiCapability[];
};

export type ModelMetadata = {
	slug: string;
	name: string;
	capabilities: AiCapability[];
};

/*
 * Service retrieval.
 * ================================================
 */

export type AvailableServicesArgs = {
	slugs?: string[];
	capabilities?: AiCapability[];
};
