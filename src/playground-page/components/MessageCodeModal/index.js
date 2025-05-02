/**
 * External dependencies
 */
import { Tabs } from '@ai-services/components';
import { Modal } from '@ai-services/interface';
import { store as aiStore } from '@ai-services/ai';

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

/**
 * Renders the modal displaying the code for a message.
 *
 * @since 0.4.0
 * @since 0.6.0 Renamed from `RawDataModal` and expanded scope.
 *
 * @return {Component} The component to be rendered.
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
			hasPhpCode: services?.[ serviceSlug ]?.type !== 'client',
		};
	} );

	const { type, content, ...additionalData } = message || {};

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
			className="ai-services-playground__message-code-modal"
		>
			{ type === 'user' && (
				<Tabs
					selectedTabId={ selectedTabId }
					onSelect={ setSelectedTabId }
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
								rawData={ additionalData.rawData }
								service={ additionalData.service }
								foundationalCapability={
									additionalData.foundationalCapability
								}
							/>
						</Tabs.TabPanel>
					) }
					<Tabs.TabPanel tabId="javascript-code">
						<JavaScriptCodeTextarea
							rawData={ additionalData.rawData }
							service={ additionalData.service }
							foundationalCapability={
								additionalData.foundationalCapability
							}
						/>
					</Tabs.TabPanel>
					<Tabs.TabPanel tabId="raw-json-data">
						<RawDataTextarea rawData={ additionalData.rawData } />
					</Tabs.TabPanel>
				</Tabs>
			) }
			{ type !== 'user' && (
				<RawDataTextarea rawData={ additionalData.rawData } />
			) }
		</Modal>
	);
}
