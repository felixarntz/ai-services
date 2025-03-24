/**
 * External dependencies
 */
import { Tabs } from '@ai-services/components';
import { Modal } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

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
 * @since n.e.x.t Renamed from `RawDataModal` and expanded scope.
 *
 * @return {Component} The component to be rendered.
 */
export default function MessageCodeModal() {
	const message = useSelect( ( select ) =>
		select( playgroundStore ).getActiveMessage()
	);
	const { type, content, ...additionalData } = message || {};

	const [ selectedTabId, setSelectedTabId ] = useState( 'php-code' );

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
						<Tabs.Tab
							tabId="php-code"
							title={ __( 'PHP code', 'ai-services' ) }
						>
							{ __( 'PHP code', 'ai-services' ) }
						</Tabs.Tab>
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
					<Tabs.TabPanel tabId="php-code">
						<PhpCodeTextarea
							rawData={ additionalData.rawData }
							service={ additionalData.service }
							foundationalCapability={
								additionalData.foundationalCapability
							}
						/>
					</Tabs.TabPanel>
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
