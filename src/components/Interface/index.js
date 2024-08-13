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
import Header from '../Header';
import Footer from '../Footer';
import Sidebar from '../Sidebar';
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
					'wp-oop-plugin-lib-example',
					'distractionFree'
				),
				previousShortcut: getAllShortcutKeyCombinations(
					'wp-oop-plugin-lib-example/previous-region'
				),
				nextShortcut: getAllShortcutKeyCombinations(
					'wp-oop-plugin-lib-example/next-region'
				),
			};
		}
	);

	const { toggle: togglePreference } = useDispatch( preferencesStore );

	useShortcut( 'wp-oop-plugin-lib-example/toggle-distraction-free', () => {
		togglePreference( 'wp-oop-plugin-lib-example', 'distractionFree' );
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
