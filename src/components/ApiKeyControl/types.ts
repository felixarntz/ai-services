/**
 * External dependencies
 */
import type { ServiceResource } from '@ai-services/ai/types';

export type ApiKeyControlProps = {
	service: ServiceResource;
	apiKey: string;
	onChangeApiKey: ( apiKey: string, serviceSlug: string ) => void;
	omitCredentialsLink?: boolean;
	className?: string;
};
