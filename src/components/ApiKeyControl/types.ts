// TODO: Replace this (incomplete) stub with a reference of the actual type (from AI package).
type AiService = {
	slug: string;
	metadata: {
		slug: string;
		name: string;
		credentials_url: string;
		type: string;
	};
	has_forced_api_key?: boolean;
};

export type ApiKeyControlProps = {
	service: AiService;
	apiKey: string;
	onChangeApiKey: ( apiKey: string, serviceSlug: string ) => void;
	omitCredentialsLink?: boolean;
	className?: string;
};
