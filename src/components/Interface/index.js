/**
 * External dependencies
 */
import clsx from 'clsx';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { InterfaceSkeleton } from '@wordpress/interface';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import {
	useShortcut,
	store as keyboardShortcutsStore,
} from '@wordpress/keyboard-shortcuts';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { default as Header, useHasHeader } from '../Header';
import HeaderActions from '../HeaderActions';
import { default as Footer, useHasFooter } from '../Footer';
import { default as Sidebar, useHasSidebar } from '../Sidebar';
import Notices from '../Notices';
import Snackbars from '../Snackbars';

/**
 * Renders the application interface wrapper.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props           Component props.
 * @param {?string} props.className Class name to add to the interface wrapper.
 * @param {Object}  props.labels    Labels for the interface areas.
 * @param {Element} props.children  Child elements to render.
 * @return {Component} The component to be rendered.
 */
export default function Interface( { className, labels, children } ) {
	const isLargeViewport = useViewportMatch( 'medium' );

	const { isDistractionFree, previousShortcut, nextShortcut } = useSelect(
		( select ) => {
			const { get } = select( preferencesStore );
			const { getAllShortcutKeyCombinations } = select(
				keyboardShortcutsStore
			);

			return {
				isDistractionFree: get(
					'ai-services',
					'distractionFree'
				),
				previousShortcut: getAllShortcutKeyCombinations(
					'ai-services/previous-region'
				),
				nextShortcut: getAllShortcutKeyCombinations(
					'ai-services/next-region'
				),
			};
		}
	);

	const { toggle: togglePreference } = useDispatch( preferencesStore );

	useShortcut( 'ai-services/toggle-distraction-free', () => {
		togglePreference( 'ai-services', 'distractionFree' );
	} );

	const hasHeader = useHasHeader();
	const header = hasHeader && (
		<div className="ais-header">
			<div className="ais-header__left">
				<Header.Slot />
			</div>
			<div className="ais-header__right">
				<HeaderActions.Slot />
			</div>
		</div>
	);

	const hasFooter = useHasFooter();
	const footer = hasFooter && (
		<div className="ais-footer">
			<Footer.Slot />
		</div>
	);

	const hasSidebar = useHasSidebar();
	const sidebar = hasSidebar && <Sidebar.Slot />;

	return (
		<InterfaceSkeleton
			enableRegionNavigation={ true }
			isDistractionFree={ isDistractionFree }
			className={ clsx( 'ais-interface', className, {
				'is-distraction-free': isDistractionFree,
			} ) }
			labels={ labels }
			header={ header }
			content={
				<>
					{ ! isDistractionFree && <Notices /> }
					{ children }
					<Snackbars />
				</>
			}
			editorNotices={ <Notices /> }
			footer={ ! isDistractionFree && isLargeViewport && footer }
			secondarySidebar={ undefined }
			sidebar={ ! isDistractionFree && sidebar }
			actions={ undefined }
			shortcuts={ {
				previous: previousShortcut,
				next: nextShortcut,
			} }
		/>
	);
}

Interface.propTypes = {
	className: PropTypes.string,
	labels: PropTypes.shape( {
		header: PropTypes.string,
		body: PropTypes.string,
		sidebar: PropTypes.string,
		secondarySidebar: PropTypes.string,
		actions: PropTypes.string,
		footer: PropTypes.string,
	} ),
	children: PropTypes.node.isRequired,
};
