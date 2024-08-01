/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { InterfaceSkeleton, ComplementaryArea } from '@wordpress/interface';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Header from '../Header';
import Footer from '../Footer';
import Notices from '../Notices';
import Snackbars from '../Snackbars';

const interfaceLabels = {
	header: __( 'Editor top bar', 'wp-oop-plugin-lib-example' ),
	body: __( 'Editor content', 'wp-oop-plugin-lib-example' ),
	secondarySdebar: __( 'Block library', 'wp-oop-plugin-lib-example' ),
	sidebar: __( 'Editor settings', 'wp-oop-plugin-lib-example' ),
	actions: __( 'Editor publish', 'wp-oop-plugin-lib-example' ),
	footer: __( 'Editor footer', 'wp-oop-plugin-lib-example' ),
};

export default function Interface( { className, children } ) {
	const isDistractionFree = false;
	const isLargeViewport = useViewportMatch( 'medium' );

	const { previousShortcut, nextShortcut } = useSelect( ( select ) => {
		return {
			previousShortcut: select(
				keyboardShortcutsStore
			).getAllShortcutKeyCombinations( 'core/editor/previous-region' ),
			nextShortcut: select(
				keyboardShortcutsStore
			).getAllShortcutKeyCombinations( 'core/editor/next-region' ),
		};
	} );

	return (
		<InterfaceSkeleton
			enableRegionNavigation={ true }
			isDistractionFree={ isDistractionFree }
			className={ clsx( 'wpoopple-interface', className, {
				'is-distraction-free': isDistractionFree,
			} ) }
			labels={ interfaceLabels }
			header={ <Header /> }
			content={
				<>
					{ ! isDistractionFree && <Notices /> }
					{ children }
					<Snackbars />
				</>
			}
			editorNotices={ <Notices /> }
			footer={ ! isDistractionFree && isLargeViewport && <Footer /> }
			secondarySidebar={ undefined }
			sidebar={
				! isDistractionFree && (
					<ComplementaryArea.Slot scope="wp-oop-plugin-lib-example/settings-screen" />
				)
			}
			actions={ <div>Actions</div> }
			shortcuts={ {
				previous: previousShortcut,
				next: nextShortcut,
			} }
		/>
	);
}
