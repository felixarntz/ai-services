/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { InterfaceSkeleton } from '@wordpress/interface';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo } from '@wordpress/element';
// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
import { __unstableUseNavigateRegions as useNavigateRegions } from '@wordpress/components';
import {
	useShortcut,
	store as keyboardShortcutsStore,
} from '@wordpress/keyboard-shortcuts';
import { useViewportMatch } from '@wordpress/compose';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';
import type { WPShortcutKeyCombination } from '@wordpress/keyboard-shortcuts';

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
import type { InterfaceProps } from './types';

/**
 * Sanitizes the shortcuts to navigate regions to be compatible with the `useNavigateRegions` hook.
 *
 * @param navigatePreviousRegionShortcut - Shortcut to navigate to the previous region.
 * @param navigateNextRegionShortcut     - Shortcut to navigate to the next region.
 * @returns Object to pass to the `useNavigateRegions` hook.
 */
function sanitizeNavigateRegions(
	navigatePreviousRegionShortcut: WPShortcutKeyCombination[],
	navigateNextRegionShortcut: WPShortcutKeyCombination[]
) {
	return {
		previous: navigatePreviousRegionShortcut.map( ( shortcut ) => {
			return {
				character: shortcut.character,
				modifier: shortcut.modifier || 'undefined',
			};
		} ),
		next: navigateNextRegionShortcut.map( ( shortcut ) => {
			return {
				character: shortcut.character,
				modifier: shortcut.modifier || 'undefined',
			};
		} ),
	};
}

/**
 * Renders the application interface wrapper.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function Interface(
	props: WordPressComponentProps< InterfaceProps, null >
) {
	const { className, labels, children } = props;

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
			isDistractionFree: !! getPreference( 'distractionFree' ),
			navigatePreviousRegionShortcut: getAllShortcutKeyCombinations(
				'wp-starter-plugin/previous-region'
			),
			navigateNextRegionShortcut: getAllShortcutKeyCombinations(
				'wp-starter-plugin/next-region'
			),
			activeSidebar: getActiveSidebar(),
			defaultSidebar: getDefaultSidebar(),
		};
	}, [] );

	const { setDefaultSidebar, toggleDefaultSidebar, togglePreference } =
		useDispatch( interfaceStore );

	useEffect( () => {
		if ( activeSidebar && ! defaultSidebar ) {
			setDefaultSidebar( activeSidebar );
		}
	}, [ activeSidebar, defaultSidebar, setDefaultSidebar ] );

	useShortcut( 'wp-starter-plugin/toggle-distraction-free', () => {
		togglePreference( 'distractionFree' );
	} );

	useShortcut( 'wp-starter-plugin/toggle-sidebar', () => {
		toggleDefaultSidebar();
	} );

	const navigateRegionsInput = useMemo(
		() =>
			sanitizeNavigateRegions(
				navigatePreviousRegionShortcut,
				navigateNextRegionShortcut
			),
		[ navigatePreviousRegionShortcut, navigateNextRegionShortcut ]
	);

	const navigateRegionsProps = useNavigateRegions( navigateRegionsInput );

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
