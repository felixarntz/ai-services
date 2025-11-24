/**
 * External dependencies
 */
import { PluginIcon } from '@ai-services/components';
import {
	App,
	Header,
	HeaderActions,
	Footer,
	Sidebar,
	PinnedSidebars,
} from 'wp-interface';

/**
 * WordPress dependencies
 */
import { __, isRTL } from '@wordpress/i18n';
import { drawerLeft, drawerRight } from '@wordpress/icons';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import PlaygroundMoreMenu from '../PlaygroundMoreMenu';
import PlaygroundStatus from '../PlaygroundStatus';
import PlaygroundMain from '../PlaygroundMain';
import FunctionDeclarationsModal from '../FunctionDeclarationsModal';
import MessageCodeModal from '../MessageCodeModal';
import PlaygroundCapabilitiesPanel from '../PlaygroundCapabilitiesPanel';
import PlaygroundServiceModelPanel from '../PlaygroundServiceModelPanel';
import PlaygroundModelConfigPanel from '../PlaygroundModelConfigPanel';
import PlaygroundFunctionDeclarationsPanel from '../PlaygroundFunctionDeclarationsPanel';
import SystemInstructionToggle from './system-instruction-toggle';
import ResetMessagesButton from './reset-messages-button';
import './style.scss';

/**
 * Renders the full playground application.
 *
 * @since 0.4.0
 *
 * @returns The component to be rendered.
 */
export default function PlaygroundApp() {
	const labels = {
		header: __( 'Playground top bar', 'ai-services' ),
		body: __( 'Playground content', 'ai-services' ),
		sidebar: __( 'Playground sidebar', 'ai-services' ),
		actions: __( 'Playground actions', 'ai-services' ),
		footer: __( 'Playground footer', 'ai-services' ),
		keyboardShortcutsModalTitle: __( 'Keyboard shortcuts', 'ai-services' ),
		keyboardShortcutsModalCloseButtonLabel: __(
			'Close keyboard shortcuts modal',
			'ai-services'
		),
		keyboardShortcutsGlobalSectionTitle: __(
			'Global shortcuts',
			'ai-services'
		),
	};

	const shortcutsDescriptions = {
		'keyboard-shortcuts': __(
			'Display these keyboard shortcuts.',
			'ai-services'
		),
		'next-region': __(
			'Navigate to the next part of the screen.',
			'ai-services'
		),
		'previous-region': __(
			'Navigate to the previous part of the screen.',
			'ai-services'
		),
		'toggle-distraction-free': __(
			'Toggle distraction free mode.',
			'ai-services'
		),
		'toggle-sidebar': __( 'Show or hide the sidebar.', 'ai-services' ),
	};

	const isLargeViewport = useViewportMatch( 'medium' );

	return (
		<App
			scope="ai-services"
			labels={ labels }
			shortcutsDescriptions={ shortcutsDescriptions }
		>
			<Header>
				<PluginIcon size={ 48 } />
				<h1
					className={
						! isLargeViewport ? 'screen-reader-text' : undefined
					}
				>
					{ __( 'AI Services', 'ai-services' ) }
					{ ': ' }
					{ __( 'Playground', 'ai-services' ) }
				</h1>
				<HeaderActions>
					<ResetMessagesButton />
					<SystemInstructionToggle />
					<PinnedSidebars />
					<PlaygroundMoreMenu />
				</HeaderActions>
			</Header>
			<PlaygroundMain />
			<FunctionDeclarationsModal />
			<MessageCodeModal />
			<Sidebar
				identifier="ai-services/playground-sidebar"
				title={ __( 'AI Configuration', 'ai-services' ) }
				icon={ isRTL() ? drawerLeft : drawerRight }
				header={
					<h2 className="interface-complementary-area-header__title">
						{ __( 'AI Configuration', 'ai-services' ) }
					</h2>
				}
				isActiveByDefault
			>
				<PlaygroundCapabilitiesPanel />
				<PlaygroundServiceModelPanel />
				<PlaygroundModelConfigPanel />
				<PlaygroundFunctionDeclarationsPanel />
			</Sidebar>
			<Footer>
				<PlaygroundStatus />
			</Footer>
		</App>
	);
}
