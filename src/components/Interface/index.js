/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { InterfaceSkeleton } from '@wordpress/interface';
import { useSelect } from '@wordpress/data';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Header from '../Header';
import Footer from '../Footer';
import Sidebar from '../Sidebar';
import Notices from '../Notices';
import Snackbars from '../Snackbars';

export default function Interface( { className, labels, children } ) {
	const isDistractionFree = false;
	const isLargeViewport = useViewportMatch( 'medium' );

	const { previousShortcut, nextShortcut } = useSelect( ( select ) => {
		return {
			previousShortcut: select(
				keyboardShortcutsStore
			).getAllShortcutKeyCombinations(
				'wp-oop-plugin-lib-example/previous-region'
			),
			nextShortcut: select(
				keyboardShortcutsStore
			).getAllShortcutKeyCombinations(
				'wp-oop-plugin-lib-example/next-region'
			),
		};
	} );

	return (
		<InterfaceSkeleton
			enableRegionNavigation={ true }
			isDistractionFree={ isDistractionFree }
			className={ clsx( 'wpoopple-interface', className, {
				'is-distraction-free': isDistractionFree,
			} ) }
			labels={ labels }
			header={ <Header.Slot /> }
			content={
				<>
					{ ! isDistractionFree && <Notices /> }
					{ children }
					<Snackbars />
				</>
			}
			editorNotices={ <Notices /> }
			footer={ ! isDistractionFree && isLargeViewport && <Footer.Slot /> }
			secondarySidebar={ undefined }
			sidebar={ ! isDistractionFree && <Sidebar.Slot /> }
			actions={ <div>Actions</div> }
			shortcuts={ {
				previous: previousShortcut,
				next: nextShortcut,
			} }
		/>
	);
}
