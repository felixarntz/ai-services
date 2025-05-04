/**
 * WordPress dependencies
 */
import { ExternalLink } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SensitiveTextControl from '../SensitiveTextControl';

/**
 * Renders a sensitive text field for an AI Services API key.
 *
 * The field will by default look like a password input, i.e. the API key is not shown. A button is available next to
 * the field to toggle showing the actual API key.
 *
 * @since 0.6.0
 *
 * @param {Object}   props                     The component props.
 * @param {Object}   props.service             Data for the relevant AI service that the API key is for. It is
 *                                             recommended to retrieve this data from the `getService( slug )` selector
 *                                             of the 'ai-services/settings' store.
 * @param {string}   props.apiKey              The AI service's API key.
 * @param {Function} props.onChangeApiKey      Listener function to call when the API key is modified via the field.
 *                                             It receives the new API key value as first parameter and the relevant AI
 *                                             service's slug as second parameter.
 * @param {boolean}  props.omitCredentialsLink Optional. By default, the control displays a link to the AI service's
 *                                             platform credentials URL, to set up or manage API keys for the service.
 *                                             If this boolean prop is set, the link will not be displayed.
 * @param {string}   props.className           Optional. Class name to set on the control wrapper element.
 * @return {Component} The component to be rendered.
 */
export default function ApiKeyControl( {
	service,
	apiKey,
	onChangeApiKey,
	omitCredentialsLink,
	className,
} ) {
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
