/**
 * External dependencies
 */
import {
	App,
	Header,
	HeaderActions,
	Footer,
	Sidebar,
	PinnedSidebars,
} from '@ai-services/interface';
import { PluginIcon } from '@ai-services/components';

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
import RawDataModal from '../RawDataModal';
import PlaygroundCapabilitiesPanel from '../PlaygroundCapabilitiesPanel';
import PlaygroundServiceModelPanel from '../PlaygroundServiceModelPanel';
import SystemInstructionToggle from './system-instruction-toggle';
import ResetMessagesButton from './reset-messages-button';
import './style.scss';

const interfaceLabels = {
	header: __( 'Playground top bar', 'ai-services' ),
	body: __( 'Playground content', 'ai-services' ),
	sidebar: __( 'Playground sidebar', 'ai-services' ),
	actions: __( 'Playground actions', 'ai-services' ),
	footer: __( 'Playground footer', 'ai-services' ),
};

/**
 * Renders the full playground application.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundApp() {
	const isLargeViewport = useViewportMatch( 'medium' );

	return (
		<App labels={ interfaceLabels }>
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
			<RawDataModal />
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
			</Sidebar>
			<Footer>
				<PlaygroundStatus />
			</Footer>
		</App>
	);
}
