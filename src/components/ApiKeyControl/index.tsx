/**
 * WordPress dependencies
 */
import { ExternalLink } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import SensitiveTextControl from '../SensitiveTextControl';
import type { ApiKeyControlProps } from './types';

/**
 * Renders a sensitive text field for an AI Services API key.
 *
 * The field will by default look like a password input, i.e. the API key is not shown. A button is available next to
 * the field to toggle showing the actual API key.
 *
 * @since 0.6.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function ApiKeyControl(
	props: WordPressComponentProps< ApiKeyControlProps, null >
) {
	const { service, apiKey, onChangeApiKey, omitCredentialsLink, className } =
		props;

	if ( ! service ) {
		return null;
	}

	return (
		<SensitiveTextControl
			className={ className }
			label={ service.metadata?.name }
			HelpContent={ () => (
				<>
					{ service.has_forced_api_key
						? sprintf(
								/* translators: %s: service name */
								__(
									'The API key for %s cannot be modified as its value is enforced via filter.',
									'ai-services'
								),
								service.metadata?.name
						  )
						: sprintf(
								/* translators: %s: service name */
								__(
									'Enter the API key for %s.',
									'ai-services'
								),
								service.metadata?.name
						  ) }{ ' ' }
					{ ! omitCredentialsLink &&
						!! service.metadata?.credentials_url && (
							<ExternalLink
								href={ service.metadata?.credentials_url }
							>
								{ createInterpolateElement(
									!! apiKey
										? sprintf(
												/* translators: %s: service name */
												__(
													'Manage<span> %s</span> API keys',
													'ai-services'
												),
												service.metadata?.name
										  )
										: sprintf(
												/* translators: %s: service name */
												__(
													'Get<span> %s</span> API key',
													'ai-services'
												),
												service.metadata?.name
										  ),
									{
										span: (
											<span className="screen-reader-text" />
										),
									}
								) }
							</ExternalLink>
						) }
				</>
			) }
			readOnly={ service.has_forced_api_key }
			disabled={ apiKey === undefined }
			value={ apiKey || '' }
			onChange={ ( value ) => onChangeApiKey( value, service.slug ) }
			buttonShowLabel={ sprintf(
				/* translators: %s: service name */
				__( 'Show API key for %s.', 'ai-services' ),
				service.metadata?.name
			) }
			buttonHideLabel={ sprintf(
				/* translators: %s: service name */
				__( 'Hide API key for %s.', 'ai-services' ),
				service.metadata?.name
			) }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);
}
