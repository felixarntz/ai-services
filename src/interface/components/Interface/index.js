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
import { useEffect } from '@wordpress/element';
// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
import { __unstableUseNavigateRegions as useNavigateRegions } from '@wordpress/components';
import {
	useShortcut,
	store as keyboardShortcutsStore,
} from '@wordpress/keyboard-shortcuts';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { store as interfaceStore } from '../../store';
import { default as Header, useHasHeader } from '../Header';
import HeaderActions from '../HeaderActions';
import { default as Footer, useHasFooter } from '../Footer';
import { default as Sidebar, useHasSidebar } from '../Sidebar';
import { default as Modal } from '../Modal';
import Notices from '../Notices';
import Snackbars from '../Snackbars';

/**
 * Renders the application interface wrapper.
 *
 * @since 0.1.0
 *
 * @param {Object}  props           Component props.
 * @param {?string} props.className Class name to add to the interface wrapper.
 * @param {Object}  props.labels    Labels for the interface areas.
 * @param {Element} props.children  Child elements to render.
 * @return {Component} The component to be rendered.
 */
export default function Interface( { className, labels, children } ) {
	const isLargeViewport = useViewportMatch( 'medium' );

	const {
		isDistractionFree,
		navigatePreviousRegionShortcut,
		navigateNextRegionShortcut,
		activeSidebar,
		defaultSidebar,
	} = useSelect( ( select ) => {
		const { getAllShortcutKeyCombinations } = select(
			keyboardShortcutsStore
		);
		const { getActiveSidebar, getDefaultSidebar, getPreference } =
			select( interfaceStore );

		return {
			isDistractionFree: getPreference( 'distractionFree' ),
			navigatePreviousRegionShortcut: getAllShortcutKeyCombinations(
				'ai-services/previous-region'
			),
			navigateNextRegionShortcut: getAllShortcutKeyCombinations(
				'ai-services/next-region'
			),
			activeSidebar: getActiveSidebar(),
			defaultSidebar: getDefaultSidebar(),
		};
	} );

	const { setDefaultSidebar, toggleDefaultSidebar, togglePreference } =
		useDispatch( interfaceStore );

	useEffect( () => {
		if ( activeSidebar && ! defaultSidebar ) {
			setDefaultSidebar( activeSidebar );
		}
	}, [ activeSidebar, defaultSidebar, setDefaultSidebar ] );

	useShortcut( 'ai-services/toggle-distraction-free', () => {
		togglePreference( 'distractionFree' );
	} );

	useShortcut( 'ai-services/toggle-sidebar', () => {
		toggleDefaultSidebar();
	} );

	const navigateRegionsProps = useNavigateRegions( {
		previous: navigatePreviousRegionShortcut,
		next: navigateNextRegionShortcut,
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
		<div { ...navigateRegionsProps } ref={ navigateRegionsProps.ref }>
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
						<Modal.Slot />
					</>
				}
				editorNotices={ <Notices /> }
				footer={ ! isDistractionFree && isLargeViewport && footer }
				secondarySidebar={ undefined }
				sidebar={ ! isDistractionFree && sidebar }
				actions={ undefined }
			/>
		</div>
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
