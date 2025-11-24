/**
 * External dependencies
 */
import { enums, store as aiStore } from '@ai-services/ai';
import type { Candidates } from '@ai-services/ai/types';
import { Tabs } from 'wp-admin-components';
import { Modal } from 'wp-interface';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import PhpCodeTextarea from './php-code-textarea';
import JavaScriptCodeTextarea from './javascript-code-textarea';
import RawDataTextarea from './raw-data-textarea';
import './style.scss';
import type { AiPlaygroundMessageAdditionalData } from '../../types';

type UserMessageRawData = Exclude<
	AiPlaygroundMessageAdditionalData[ 'rawData' ],
	Candidates | undefined
>;

/**
 * Renders the modal displaying the code for a message.
 *
 * @since 0.4.0
 * @since 0.6.0 Renamed from `RawDataModal` and expanded scope.
 *
 * @returns The component to be rendered.
 */
export default function MessageCodeModal() {
	// For 'client' type services, PHP code is irrelevant because they cannot be used on the server.
	const { message, hasPhpCode } = useSelect( ( select ) => {
		const theMessage = select( playgroundStore ).getActiveMessage();
		const serviceSlug = theMessage?.service?.slug;
		if ( ! serviceSlug ) {
			return {
				message: theMessage,
				hasPhpCode: true,
			};
		}
		const services = select( aiStore ).getServices();
		return {
			message: theMessage,
			hasPhpCode:
				services?.[ serviceSlug ]?.metadata?.type !==
				enums.ServiceType.CLIENT,
		};
	}, [] );

	const { type, content, ...additionalDataFromMessage } = message
		? message
		: {};
	const additionalData =
		additionalDataFromMessage as AiPlaygroundMessageAdditionalData;

	const [ selectedTabId, setSelectedTabId ] = useState( 'php-code' );

	useEffect( () => {
		if ( type === 'user' && selectedTabId === 'php-code' && ! hasPhpCode ) {
			setSelectedTabId( 'javascript-code' );
		}
	}, [ type, hasPhpCode, selectedTabId ] );

	return (
		<Modal
			identifier="message-code"
			title={
				type === 'user'
					? __( 'Message code', 'ai-services' )
					: __( 'Message raw JSON data', 'ai-services' )
			}
			closeButtonLabel={ __( 'Close modal', 'ai-services' ) }
			className="ai-services-playground__message-code-modal"
		>
			{ type === 'user' &&
				additionalData.rawData &&
				additionalData.service &&
				additionalData.foundationalCapability && (
					<Tabs
						selectedTabId={ selectedTabId }
						onSelect={ ( id: string | null | undefined ) =>
							id && setSelectedTabId( id )
						}
						orientation="horizontal"
					>
						<Tabs.TabList className="ai-services-playground__message-code-tabs">
							{ hasPhpCode && (
								<Tabs.Tab
									tabId="php-code"
									title={ __( 'PHP code', 'ai-services' ) }
								>
									{ __( 'PHP code', 'ai-services' ) }
								</Tabs.Tab>
							) }
							<Tabs.Tab
								tabId="javascript-code"
								title={ __( 'JavaScript code', 'ai-services' ) }
							>
								{ __( 'JavaScript code', 'ai-services' ) }
							</Tabs.Tab>
							<Tabs.Tab
								tabId="raw-json-data"
								title={ __( 'Raw JSON data', 'ai-services' ) }
							>
								{ __( 'Raw JSON data', 'ai-services' ) }
							</Tabs.Tab>
						</Tabs.TabList>
						{ hasPhpCode && (
							<Tabs.TabPanel tabId="php-code">
								<PhpCodeTextarea
									rawData={
										additionalData.rawData as UserMessageRawData
									}
									service={ additionalData.service }
									foundationalCapability={
										additionalData.foundationalCapability
									}
								/>
							</Tabs.TabPanel>
						) }
						<Tabs.TabPanel tabId="javascript-code">
							<JavaScriptCodeTextarea
								rawData={
									additionalData.rawData as UserMessageRawData
								}
								service={ additionalData.service }
								foundationalCapability={
									additionalData.foundationalCapability
								}
							/>
						</Tabs.TabPanel>
						<Tabs.TabPanel tabId="raw-json-data">
							<RawDataTextarea
								rawData={ additionalData.rawData }
							/>
						</Tabs.TabPanel>
					</Tabs>
				) }
			{ type !== 'user' && additionalData.rawData && (
				<RawDataTextarea rawData={ additionalData.rawData } />
			) }
		</Modal>
	);
}
