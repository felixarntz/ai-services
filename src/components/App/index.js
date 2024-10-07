/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { SlotFillProvider } from '@wordpress/components';
import { ErrorBoundary } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import Interface from '../Interface';
import ShortcutsRegister from '../ShortcutsRegister';
import KeyboardShortcutsHelpModal from '../KeyboardShortcutsHelpModal';
import './style.scss';

/**
 * Renders the root of the application.
 *
 * @since 0.1.0
 *
 * @param {Object}  props           Component props.
 * @param {?string} props.className Class name to add to the interface wrapper.
 * @param {Object}  props.labels    Labels for the interface areas.
 * @param {Element} props.children  Child elements to render.
 * @return {Component} The component to be rendered.
 */
export default function App( { className, labels, children } ) {
	return (
		<SlotFillProvider>
			<ErrorBoundary>
				<Interface className={ className } labels={ labels }>
					{ children }
				</Interface>
				<ShortcutsRegister />
				<KeyboardShortcutsHelpModal />
			</ErrorBoundary>
		</SlotFillProvider>
	);
}

App.propTypes = {
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
