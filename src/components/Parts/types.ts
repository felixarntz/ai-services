export type MediaProps = {
	mimeType: string;
	src: string;
};

export type JsonTextareaProps = {
	data: unknown;
	label: string;
};

// TODO: Replace these stubs with a reference of the actual types (from AI package).
type TextPart = {
	text: string;
};
type InlineDataPart = {
	inlineData: {
		mimeType: string;
		data: string;
	};
};
type FileDataPart = {
	fileData: {
		mimeType: string;
		fileUri: string;
	};
};
type FunctionCallPart = {
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
type FunctionResponsePart = {
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
type Part =
	| TextPart
	| InlineDataPart
	| FileDataPart
	| FunctionCallPart
	| FunctionResponsePart;

export type PartsProps = {
	parts: Part[];
};
